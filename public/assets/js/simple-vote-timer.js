/**
 * Простой таймер голосования
 */
(function() {
    'use strict';
    
    console.log('Simple Vote Timer: Starting...');
    
    // Проверяем, есть ли элементы голосования на странице
    function findVoteElements() {
        const elements = {
            alert: document.querySelector('.alert[data-vote-timer]') || 
                   document.querySelector('.alert'),
            button: document.querySelector('a[href*="vote"]') || 
                   document.querySelector('button[data-item-id]'),
            container: document.querySelector('.card.account-card .card-body') ||
                      document.querySelector('[data-vote-timer]')
        };
        
        console.log('Simple Vote Timer: Elements found:', {
            alert: elements.alert ? 'Yes' : 'No',
            button: elements.button ? 'Yes' : 'No', 
            container: elements.container ? 'Yes' : 'No'
        });
        
        return elements;
    }
    
    // Обновляем отображение
    function updateDisplay(voteInfo) {
        console.log('Simple Vote Timer: Updating display with:', voteInfo);
        
        const elements = findVoteElements();
        if (!elements.container) {
            console.log('Simple Vote Timer: No container found');
            return;
        }
        
        if (voteInfo.can_vote) {
            // Можно голосовать
            if (elements.alert) {
                elements.alert.className = 'alert alert-success mb-3';
                elements.alert.innerHTML = '<i class="fas fa-check-circle"></i> Можно голосовать прямо сейчас!';
            }
            
            if (elements.button) {
                elements.button.className = 'btn btn-primary';
                elements.button.disabled = false;
                elements.button.innerHTML = '<i class="fas fa-vote-yea"></i> Голосовать на MMOTOP';
            }
        } else {
            // Кулдаун активен
            if (elements.alert) {
                elements.alert.className = 'alert alert-warning mb-3';
                elements.alert.innerHTML = `<i class="fas fa-clock"></i> Следующее голосование доступно через: ${voteInfo.remaining_time}`;
            }
            
            if (elements.button) {
                elements.button.className = 'btn btn-secondary';
                elements.button.disabled = true;
                elements.button.innerHTML = '<i class="fas fa-vote-yea"></i> Голосовать на MMOTOP';
            }
        }
    }
    
    // Получаем информацию о голосовании
    async function fetchVoteInfo() {
        try {
            console.log('Simple Vote Timer: Fetching vote info...');
            const response = await fetch('/vote/info', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            if (!response.ok) {
                console.error('Simple Vote Timer: Failed to fetch vote info, status:', response.status);
                return;
            }
            
            const data = await response.json();
            console.log('Simple Vote Timer: Vote info received:', data);
            
            if (data.success) {
                updateDisplay(data);
            }
        } catch (error) {
            console.error('Simple Vote Timer: Error fetching vote info:', error);
        }
    }
    
    // Инициализация
    function init() {
        console.log('Simple Vote Timer: Initializing...');
        
        const elements = findVoteElements();
        if (!elements.container) {
            console.log('Simple Vote Timer: No vote elements found on this page');
            return;
        }
        
        console.log('Simple Vote Timer: Vote elements found, starting timer...');
        
        // Запускаем обновление
        fetchVoteInfo();
        
        // Обновляем каждую минуту
        setInterval(fetchVoteInfo, 60000);
    }
    
    // Запускаем при загрузке страницы
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
