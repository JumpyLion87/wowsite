/**
 * Таймер голосования с учетом времени mmotop
 */
class VoteTimer {
    constructor() {
        this.updateInterval = 60000; // Обновление каждую минуту
        this.init();
    }

    init() {
        console.log('VoteTimer: Initializing...');
        
        // Проверяем есть ли таймер на странице
        const voteTimerElement = document.querySelector('[data-vote-timer]');
        console.log('VoteTimer: Timer element found:', voteTimerElement ? 'Yes' : 'No');
        
        if (!voteTimerElement) {
            console.log('VoteTimer: No timer element found, checking for vote container...');
            const voteContainer = document.querySelector('.card.account-card .card-body');
            console.log('VoteTimer: Vote container found:', voteContainer ? 'Yes' : 'No');
            if (!voteContainer) {
                console.log('VoteTimer: No vote container found, trying alternative selectors...');
                // Попробуем другие селекторы
                const altContainer = document.querySelector('[data-vote-timer]') || 
                                   document.querySelector('.alert') || 
                                   document.querySelector('.vote-section');
                console.log('VoteTimer: Alternative container found:', altContainer ? 'Yes' : 'No');
                if (!altContainer) {
                    console.log('VoteTimer: No vote elements found, exiting');
                    return;
                }
            }
        }

        console.log('VoteTimer: Starting timer updates...');
        // Запускаем обновление таймера
        this.updateTimer();
        setInterval(() => this.updateTimer(), this.updateInterval);
    }

    async updateTimer() {
        try {
            console.log('VoteTimer: Updating timer...');
            const response = await fetch('/vote/info', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                console.error('VoteTimer: Failed to fetch vote info, status:', response.status);
                return;
            }

            const data = await response.json();
            console.log('VoteTimer: Vote info received:', data);
            
            if (data.success) {
                this.updateVoteDisplay(data);
            }
        } catch (error) {
            console.error('VoteTimer: Error updating vote timer:', error);
        }
    }

    updateVoteDisplay(voteInfo) {
        console.log('VoteTimer: Updating vote display...');
        let voteContainer = document.querySelector('.card.account-card .card-body');
        if (!voteContainer) {
            console.log('VoteTimer: No vote container found, trying alternative selectors...');
            voteContainer = document.querySelector('[data-vote-timer]') || 
                          document.querySelector('.alert') || 
                          document.querySelector('.vote-section');
        }
        
        if (!voteContainer) {
            console.log('VoteTimer: No vote container found');
            return;
        }

        // Находим элементы для обновления
        const alertElement = voteContainer.querySelector('.alert');
        const buttonElement = voteContainer.querySelector('a[href*="vote"], button');
        
        console.log('VoteTimer: Elements found - Alert:', alertElement ? 'Yes' : 'No', 'Button:', buttonElement ? 'Yes' : 'No');

        if (voteInfo.can_vote) {
            // Можно голосовать
            if (alertElement) {
                alertElement.className = 'alert alert-success mb-3';
                alertElement.innerHTML = '<i class="fas fa-check-circle"></i> Можно голосовать прямо сейчас!';
            }
            
            if (buttonElement) {
                buttonElement.className = 'btn btn-primary';
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-vote-yea"></i> Голосовать на MMOTOP';
            }
        } else {
            // Кулдаун активен
            if (alertElement) {
                alertElement.className = 'alert alert-warning mb-3';
                alertElement.innerHTML = `<i class="fas fa-clock"></i> Следующее голосование доступно через: ${voteInfo.remaining_time}`;
            }
            
            if (buttonElement) {
                buttonElement.className = 'btn btn-secondary';
                buttonElement.disabled = true;
                buttonElement.innerHTML = '<i class="fas fa-vote-yea"></i> Голосовать на MMOTOP';
            }
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    new VoteTimer();
});
