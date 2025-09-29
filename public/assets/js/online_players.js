// Online Players Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 60 seconds
    let refreshInterval = setInterval(function() {
        refreshOnlinePlayers();
    }, 60000);
    
    // Add click handler for player rows
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', function() {
            const playerName = this.querySelector('.player-name')?.textContent;
            if (playerName) {
                console.log('Selected player:', playerName);
                // Additional functionality can be implemented here
            }
        });
    });
    
    // Add refresh button functionality
    const refreshBtn = document.querySelector('.refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            refreshOnlinePlayers();
        });
    }
});

// Function to refresh online players data
function refreshOnlinePlayers() {
    // Show loading state
    const refreshBtn = document.querySelector('.refresh-btn');
    if (refreshBtn) {
        const originalHtml = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        refreshBtn.disabled = true;
        
        // Reload page after a short delay to show loading state
        setTimeout(() => {
            location.reload();
        }, 500);
    } else {
        location.reload();
    }
}

// Function to update online count without full page reload (future enhancement)
function updateOnlineCount(count) {
    const onlineCountElement = document.querySelector('.online-count strong');
    if (onlineCountElement) {
        onlineCountElement.textContent = count;
    }
}

// Function to handle AJAX updates (future enhancement)
function fetchOnlinePlayers() {
    // This would be implemented for partial updates without full page reload
    fetch('/api/online_players')
        .then(response => response.json())
        .then(data => {
            updateOnlineCount(data.total_online);
            // Update other elements as needed
        })
        .catch(error => {
            console.error('Error fetching online players:', error);
        });
}