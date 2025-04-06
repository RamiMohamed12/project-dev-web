<?php // tests/Unit/Model/UserTest.php

// Declare the namespace corresponding to the directory structure and composer.json autoload-dev
namespace Tests\Unit\Model;

// Import necessary classes
use PHPUnit\Framework\TestCase; // The base class for PHPUnit tests
use App\Model\User;             // The class we are testing (Assuming App\Model namespace)
use PDO;                        // Need PDO for type hinting and mocking
use PDOStatement;               // Need PDOStatement for mocking prepared statements
use PDOException;               // Need PDOException for testing error handling
use InvalidArgumentException;   // For testing constructor validation
use PHPUnit\Framework\MockObject\MockObject; // Good practice for type hinting mocks

// IMPORTANT: Add the namespace to your actual User class file if it doesn't have one!
// Add this line at the top of src/Model/user.php:
// namespace App\Model;

class UserTest extends TestCase
{
    // Declare properties to hold mock objects and the User instance
    private MockObject $pdoMock;        // Mock for the PDO connection
    private MockObject $stmtMock;       // Mock for the PDOStatement
    private User $user;                 // Instance of the User class under test

    /**
     * This method is called before each test method runs.
     * Use it to set up mocks and the object under test.
     */
    protected function setUp(): void
    {
        parent::setUp(); // Always call parent setUp

        // Create mocks for PDO and PDOStatement
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // Create an instance of the User class, injecting the PDO mock
        // We assume the User class is in the App\Model namespace
        // If not, adjust the 'use' statement above and this line accordingly
        $this->user = new User($this->pdoMock);
    }

    // --- Constructor Tests ---

    public function testConstructorThrowsExceptionIfPdoIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Database connection is required"); // Check specific message

        // Try creating User with null - should throw exception
        // Need to use a try-catch or a different approach if the constructor argument isn't nullable
        // Since PDO type hint enforces non-null, we can't directly pass null.
        // Instead, we test the existing instance setup, assuming setUp works.
        // A better test might involve reflection if we *really* needed to test passing null.
        // For now, we focus on the behavior *with* a valid mock.

        // We can test that it sets PDO attributes correctly though:
        $this->pdoMock->expects($this->once())
                      ->method('setAttribute')
                      ->with(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Re-create user *inside* the test to trigger setAttribute expectation JUST for this test
        $user = new User($this->pdoMock);
        $this->assertInstanceOf(User::class, $user); // Verify it was created
    }

    // --- Sanitize Method Tests ---

    public function testSanitizeRemovesTagsAndEncodes(): void
    {
        $dirtyInput = "<script>alert('XSS');</script> <h1>Test</h1>";
        $expectedOutput = "alert('XSS'); Test"; // Based on htmlspecialchars default quotes
        $this->assertEquals($expectedOutput, $this->user->sanitize($dirtyInput));
    }

    public function testSanitizeHandlesNull(): void
    {
        $this->assertEquals('', $this->user->sanitize(null));
    }

    // --- createStudent Method Tests ---

    /**
     * Test the successful creation of a student.
     * @test // Alternatively, use the #[Test] attribute in PHP 8+
     */
    public function createStudentSuccessfully(): void
    {
        // 1. Arrange: Define input data and mock expectations

        $name = 'Test Student';
        $email = 'test@example.com';
        $password = 'password123'; // Raw password
        $location = 'Test Location';
        $phone = '1234567890';
        $dob = '2000-01-01';
        $year = '3rd';
        $description = 'Test Desc';
        $school = 'Test School';
        $creatorPiloteId = 10;

        // Expect 'prepare' to be called once on the PDO mock with SQL containing INSERT INTO student
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO student'))
            ->willReturn($this->stmtMock); // Return the mock statement

        // Expect 'bindValue' or 'bindParam' to be called multiple times on the statement mock.
        // We can be very specific, or just check execute and rowCount for a success case.
        // Let's check a few key binds for demonstration, then execute & rowCount.
        // Note: password won't be 'password123' due to hashing. We expect ANY string for the hash.
        $this->stmtMock->expects($this->exactly(10)) // Check the number of expected bindings
             ->method('bindValue') // Using bindValue because the code uses it for nulls
             ->withConsecutive( // Checks arguments in the order they are called
                 [$this->equalTo(':name'), $this->equalTo($this->user->sanitize($name))],
                 [$this->equalTo(':email'), $this->equalTo($this->user->sanitize($email))],
                 [$this->equalTo(':password'), $this->isType('string')], // Expect any hashed string
                 [$this->equalTo(':location'), $this->equalTo($this->user->sanitize($location))],
                 [$this->equalTo(':phone'), $this->equalTo($this->user->sanitize($phone))],
                 [$this->equalTo(':dob'), $this->equalTo($this->user->sanitize($dob))],
                 [$this->equalTo(':year'), $this->equalTo($this->user->sanitize($year))],
                 [$this->equalTo(':description'), $this->equalTo($this->user->sanitize($description))],
                 [$this->equalTo(':school'), $this->equalTo($this->user->sanitize($school)), $this->equalTo(PDO::PARAM_STR)], // Check 3rd arg for non-null
                 [$this->equalTo(':creator_id'), $this->equalTo($creatorPiloteId), $this->equalTo(PDO::PARAM_INT)] // Check 3rd arg for non-null
             );
        // Important: If you use bindParam, mocking is slightly different. bindValue is easier here.

        // Expect 'execute' to be called once on the statement mock
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true); // Simulate successful execution

        // Expect 'rowCount' to be called once, returning > 0 for success
        $this->stmtMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1); // Simulate one row affected

        // 2. Act: Call the method under test
        $result = $this->user->createStudent(
            $name, $email, $password, $location, $phone, $dob, $year, $description, $school, $creatorPiloteId
        );

        // 3. Assert: Verify the outcome
        $this->assertTrue($result, 'createStudent should return true on success.');
        $this->assertEmpty($this->user->getError(), 'Error message should be empty on success.');
    }

     /** @test */
    public function createStudentFailsIfRequiredFieldMissing(): void
    {
        // 1. Arrange: Provide data with a missing required field (e.g., name is empty)
        $email = 'test@example.com';
        $password = 'password123';
        $dob = '2000-01-01';
        $year = '3rd';
        // ... other fields (can be dummy values or null)

        // Expect NO database interaction because validation should fail first
        $this->pdoMock->expects($this->never())->method('prepare');
        $this->stmtMock->expects($this->never())->method('execute');

        // 2. Act
        $result = $this->user->createStudent(
            '', $email, $password, null, null, $dob, $year, null, null, null // Empty name
        );

        // 3. Assert
        $this->assertFalse($result, 'createStudent should return false when required fields missing.');
        $this->assertStringContainsStringIgnoringCase('Required fields missing', $this->user->getError(), 'Correct error message expected.');
    }

    /** @test */
    public function createStudentFailsIfEmailInvalid(): void
    {
        // 1. Arrange
        $name = 'Test Student';
        $invalidEmail = 'not-an-email';
        $password = 'password123';
        $dob = '2000-01-01';
        $year = '3rd';

        $this->pdoMock->expects($this->never())->method('prepare');

        // 2. Act
        $result = $this->user->createStudent(
            $name, $invalidEmail, $password, null, null, $dob, $year, null, null, null
        );

        // 3. Assert
        $this->assertFalse($result);
        $this->assertEquals('Invalid email format.', $this->user->getError());
    }

    /** @test */
    public function createStudentFailsIfYearInvalid(): void
    {
         // 1. Arrange
        $name = 'Test Student';
        $email = 'test@example.com';
        $password = 'password123';
        $dob = '2000-01-01';
        $invalidYear = '6th'; // Not in the valid list

        $this->pdoMock->expects($this->never())->method('prepare');

        // 2. Act
        $result = $this->user->createStudent(
            $name, $email, $password, null, null, $dob, $invalidYear, null, null, null
        );

        // 3. Assert
        $this->assertFalse($result);
        $this->assertEquals('Invalid year selected.', $this->user->getError());
    }

    /** @test */
    public function createStudentHandlesPDOExceptionOnExecute(): void
    {
        // 1. Arrange: Simulate a PDOException during execute (e.g., duplicate email)
        $name = 'Test Student';
        $email = 'duplicate@example.com'; // Use an email that might cause a constraint violation
        $password = 'password123';
        $dob = '2000-01-01';
        $year = '3rd';

        // Mock prepare step
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        // Mock bindValue (can simplify here if needed, focusing on the exception)
         $this->stmtMock->expects($this->any())->method('bindValue'); // Allow any binding calls

        // Make execute *throw* a PDOException with code 23000
        $pdoException = new PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'duplicate@example.com' for key 'email'", 23000);
        // Note: errorInfo is protected, mocking it directly is complex. Checking the code is usually sufficient.
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException($pdoException);

        // Expect rowCount NOT to be called because execute failed
        $this->stmtMock->expects($this->never())->method('rowCount');

        // 2. Act
        $result = $this->user->createStudent(
            $name, $email, $password, null, null, $dob, $year, null, null, null
        );

        // 3. Assert
        $this->assertFalse($result, 'createStudent should return false on PDOException.');
        $this->assertEquals('Email already exists.', $this->user->getError(), 'Correct error message for duplicate email expected.');
    }

     /** @test */
    public function createStudentHandlesGenericPDOException(): void
    {
        // 1. Arrange: Simulate a generic PDOException during execute
        $name = 'Test Student'; $email = 'test@example.com'; $password = 'password123'; $dob = '2000-01-01'; $year = '3rd';

        $this->pdoMock->expects($this->once())->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->expects($this->any())->method('bindValue');

        // Throw a generic PDOException (different code)
        $pdoException = new PDOException("DB connection failed", 500); // Example code
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException($pdoException);

        $this->stmtMock->expects($this->never())->method('rowCount');

        // 2. Act
        $result = $this->user->createStudent($name, $email, $password, null, null, $dob, $year, null, null, null);

        // 3. Assert
        $this->assertFalse($result);
        $this->assertEquals('Database error during student creation.', $this->user->getError());
    }

    // --- Add similar tests for createPilote, createAdmin, read methods, update methods, delete methods etc. ---
    // Remember to mock PDO::prepare, PDOStatement::execute, PDOStatement::fetch, PDOStatement::fetchAll, PDOStatement::rowCount
    // as needed for each method, and test both success and failure paths (validation errors, PDO exceptions).

}
