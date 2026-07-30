/**
 * ModalManager
 * 
 * Единая система управления модальными окнами
 * Поддержка анимаций, закрытия по Escape и клику вне окна
 */
export class ModalManager {
    constructor() {
        this.activeModals = new Set();
        this.initGlobalListeners();
    }

    /**
     * Открыть модальное окно
     */
    open(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.warn(`Modal with id "${modalId}" not found`);
            return;
        }

        // Показываем модальное окно с анимацией
        modal.classList.remove('hidden');
        
        // Небольшая задержка для срабатывания CSS transition
        requestAnimationFrame(() => {
            modal.classList.add('modal-enter-active');
            
            // Фокусируемся на первом интерактивном элементе
            const firstFocusable = modal.querySelector(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            firstFocusable?.focus();
        });

        this.activeModals.add(modalId);
        
        // Блокируем скролл на body
        document.body.style.overflow = 'hidden';
        
        // Добавляем оверлей
        this.ensureOverlay();
    }

    /**
     * Закрыть модальное окно
     */
    close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('modal-leave-active');
        
        // Ждем окончания анимации перед скрытием
        const onAnimationEnd = () => {
            modal.classList.add('hidden');
            modal.classList.remove('modal-enter-active', 'modal-leave-active');
            modal.removeEventListener('transitionend', onAnimationEnd);
        };
        
        modal.addEventListener('transitionend', onAnimationEnd);
        
        this.activeModals.delete(modalId);
        
        // Восстанавливаем скролл только если нет других открытых модалок
        if (this.activeModals.size === 0) {
            document.body.style.overflow = '';
            this.removeOverlay();
        }
    }

    /**
     * Закрыть все модальные окна
     */
    closeAll() {
        this.activeModals.forEach(modalId => {
            this.close(modalId);
        });
    }

    /**
     * Глобальные обработчики
     */
    initGlobalListeners() {
        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModals.size > 0) {
                const lastModal = [...this.activeModals].pop();
                this.close(lastModal);
            }
        });
    }

    /**
     * Оверлей для модальных окон
     */
    ensureOverlay() {
        if (!document.getElementById('modal-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'modal-overlay';
            overlay.className = 'modal-overlay';
            overlay.addEventListener('click', () => {
                const lastModal = [...this.activeModals].pop();
                if (lastModal) {
                    this.close(lastModal);
                }
            });
            document.body.appendChild(overlay);
        }
    }

    removeOverlay() {
        document.getElementById('modal-overlay')?.remove();
    }
}