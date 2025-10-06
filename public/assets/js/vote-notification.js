/**
 * Система уведомлений о голосовании
 */

class VoteNotification {
    constructor() {
        this.checkInterval = 5 * 60 * 1000; // Проверка каждые 5 минут
        this.notificationTimeout = 10000; // Уведомление показывается 10 секунд
        this.init();
    }

    init() {
        // Проверяем при загрузке страницы
        this.checkVote();
        
        // Периодическая проверка
        setInterval(() => this.checkVote(), this.checkInterval);
        
        // Показываем уведомление если оно есть в сессии
        this.showSessionNotification();
    }

    /**
     * Проверить голос через AJAX
     */
    async checkVote() {
        try {
            const response = await fetch('/vote/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (data.success && data.points > 0) {
                this.showNotification(data.message, 'success', data.points);
            }
        } catch (error) {
            console.error('Vote check error:', error);
        }
    }

    /**
     * Показать уведомление из сессии (при загрузке страницы)
     */
    showSessionNotification() {
        const notification = document.querySelector('[data-vote-notification]');
        if (notification) {
            const type = notification.dataset.notificationType || 'success';
            const message = notification.dataset.notificationMessage || '';
            const points = notification.dataset.notificationPoints || 0;
            
            this.showNotification(message, type, points);
            notification.remove();
        }
    }

    /**
     * Показать всплывающее уведомление
     */
    showNotification(message, type = 'success', points = 0) {
        // Создаем контейнер для уведомлений если его нет
        let container = document.getElementById('vote-notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'vote-notifications-container';
            container.className = 'vote-notifications-container';
            document.body.appendChild(container);
        }

        // Создаем уведомление
        const notification = document.createElement('div');
        notification.className = `vote-notification vote-notification-${type} vote-notification-enter`;
        
        notification.innerHTML = `
            <div class="vote-notification-icon">
                ${type === 'success' ? '✓' : 'ℹ'}
            </div>
            <div class="vote-notification-content">
                <div class="vote-notification-title">
                    ${type === 'success' ? 'Голос учтен!' : 'Уведомление'}
                </div>
                <div class="vote-notification-message">${message}</div>
                ${points > 0 ? `<div class="vote-notification-points">+${points} поинтов</div>` : ''}
            </div>
            <button class="vote-notification-close" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(notification);

        // Анимация появления
        setTimeout(() => {
            notification.classList.remove('vote-notification-enter');
            notification.classList.add('vote-notification-show');
        }, 10);

        // Автоматическое скрытие
        setTimeout(() => {
            this.hideNotification(notification);
        }, this.notificationTimeout);

        // Звуковой эффект (опционально)
        if (type === 'success') {
            this.playNotificationSound();
        }
    }

    /**
     * Скрыть уведомление
     */
    hideNotification(notification) {
        notification.classList.remove('vote-notification-show');
        notification.classList.add('vote-notification-exit');
        
        setTimeout(() => {
            notification.remove();
        }, 300);
    }

    /**
     * Воспроизвести звук уведомления
     */
    playNotificationSound() {
        // Можно добавить звуковой файл
        // const audio = new Audio('/assets/sounds/notification.mp3');
        // audio.volume = 0.3;
        // audio.play().catch(e => console.log('Sound play failed:', e));
    }

    /**
     * Получить информацию о голосовании
     */
    async getVoteInfo() {
        try {
            const response = await fetch('/vote/info', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) {
                return null;
            }

            return await response.json();
        } catch (error) {
            console.error('Vote info error:', error);
            return null;
        }
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Проверяем что пользователь авторизован
    if (document.querySelector('meta[name="user-authenticated"]')?.content === 'true') {
        new VoteNotification();
    }
});

// Экспорт для использования в других скриптах
window.VoteNotification = VoteNotification;
