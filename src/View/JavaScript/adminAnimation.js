document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');
            
            // Check for saved theme preference or use system preference
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                body.classList.add('dark-mode');
                icon.classList.replace('fa-moon', 'fa-sun');
            }
            
            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
                
                if (body.classList.contains('dark-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('theme', 'dark');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('theme', 'light');
                }
            });
            
            // Update time and date
            function updateDateTime() {
                const now = new Date();
                
                // Update time
                const timeElement = document.getElementById('currentTime');
                if (timeElement) {
                    timeElement.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                
                // Update date
                const dateElement = document.getElementById('currentDate');
                if (dateElement) {
                    const options = { weekday: 'long', month: 'short', day: 'numeric' };
                    dateElement.textContent = now.toLocaleDateString([], options);
                }
            }
            
            // Initial update
            updateDateTime();
            
            // Update every minute
            setInterval(updateDateTime, 60000);
            
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target) && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                });
            }
            
            // Add hover animations to bento items
            const bentoItems = document.querySelectorAll('.bento-item');
            
            bentoItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'var(--card-shadow)';
                });
            });
        });
