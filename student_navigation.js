// Global navigation functions for student dashboard
window.loadPage = function(page) {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) {
        console.error('main-content element not found');
        return;
    }
    
    fetch(page)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            mainContent.innerHTML = html;
            // Re-initialize any scripts in the loaded content
            executeScripts(mainContent);
        })
        .catch(err => {
            console.error('Error loading page:', err);
            mainContent.innerHTML = '<div class="alert alert-danger">Failed to load page. Please try again.</div>';
        });
};

window.viewCourse = function(courseId) {
    loadPage('course_detail.php?id=' + courseId);
};

window.goBackToCourses = function() {
    loadPage('course.php');
};

// Execute scripts in dynamically loaded content
function executeScripts(container) {
    const scripts = container.querySelectorAll('script');
    scripts.forEach(script => {
        const newScript = document.createElement('script');
        if (script.src) {
            newScript.src = script.src;
        } else {
            newScript.textContent = script.textContent;
        }
        document.body.appendChild(newScript);
        document.body.removeChild(newScript);
    });
}
