/**
 * Таймер голосования с учетом времени mmotop
 */
class VoteTimer {
    constructor() {
        this.updateInterval = 60000; // Обновление каждую минуту
        this.init();
    }

    init() {
        // Проверяем есть ли таймер на странице
        const voteTimerElement = document.querySelector('[data-vote-timer]');
        if (!voteTimerElement) {
            return;
        }

        // Запускаем обновление таймера
        this.updateTimer();
        setInterval(() => this.updateTimer(), this.updateInterval);
    }

    async updateTimer() {
        try {
            const response = await fetch('/vote/info', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                console.error('Failed to fetch vote info');
                return;
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateVoteDisplay(data);
            }
        } catch (error) {
            console.error('Error updating vote timer:', error);
        }
    }

    updateVoteDisplay(voteInfo) {
        const voteContainer = document.querySelector('.card.account-card .card-body');
        if (!voteContainer) return;

        // Находим элементы для обновления
        const alertElement = voteContainer.querySelector('.alert');
        const buttonElement = voteContainer.querySelector('a[href*="vote"], button');

        if (voteInfo.can_vote) {
            // Можно голосовать
            if (alertElement) {
                alertElement.className = 'alert alert-success mb-3';
                alertElement.innerHTML = '<i class="fas fa-check-circle"></i> {{ __("vote.can_vote_now") }}';
            }
            
            if (buttonElement) {
                buttonElement.className = 'btn btn-primary';
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="fas fa-vote-yea"></i> {{ __("vote.vote_button") }}';
            }
        } else {
            // Кулдаун активен
            if (alertElement) {
                alertElement.className = 'alert alert-warning mb-3';
                alertElement.innerHTML = `<i class="fas fa-clock"></i> {{ __('vote.remaining_time', ['time' => '${voteInfo.remaining_time}']) }}`;
            }
            
            if (buttonElement) {
                buttonElement.className = 'btn btn-secondary';
                buttonElement.disabled = true;
                buttonElement.innerHTML = '<i class="fas fa-vote-yea"></i> {{ __("vote.vote_button") }}';
            }
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    new VoteTimer();
});
