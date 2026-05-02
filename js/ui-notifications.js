(function(window) {
    const EducattioUI = {
        /**
         * Shows a success or error toast.
         * @param {string} message 
         * @param {string} type 'success' | 'error'
         */
        notify: function(message, type = 'success') {
            const containerId = 'educattio-toast-container';
            let container = document.getElementById(containerId);
            
            if (!container) {
                container = document.createElement('div');
                container.id = containerId;
                container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                `;
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const color = type === 'success' ? '#10b981' : '#ef4444';
            
            toast.style.cssText = `
                background: white;
                color: #1f2937;
                padding: 12px 20px;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 280px;
                border-left: 5px solid ${color};
                transform: translateX(120%);
                transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                font-family: sans-serif;
                font-size: 0.95rem;
            `;
            
            toast.innerHTML = `
                <i class="fas ${icon}" style="color: ${color}; font-size: 1.2rem;"></i>
                <span style="flex-grow: 1;">${message}</span>
                <button style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0 0 0 10px;">
                    <i class="fas fa-times"></i>
                </button>
            `;

            container.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => toast.style.transform = 'translateX(0)', 10);

            const close = () => {
                toast.style.transform = 'translateX(120%)';
                setTimeout(() => toast.remove(), 300);
            };

            const closeBtn = toast.querySelector('button');
            if (closeBtn) closeBtn.onclick = close;
            setTimeout(close, 5000);
        },

        success: function(message) { this.notify(message, 'success'); },
        error: function(message) { this.notify(message, 'error'); },

        /**
         * Shows a premium confirmation modal.
         * @param {string} message 
         * @param {string} title 
         * @returns {Promise<boolean>}
         */
        confirm: function(message, title = '¿Estás seguro?') {
            console.log("EducattioUI.confirm called with message:", message);
            return new Promise((resolve) => {
                const modalId = 'educattio-confirm-modal';
                let modalEl = document.getElementById(modalId);
                
                if (modalEl) {
                    const oldInstance = bootstrap.Modal.getInstance(modalEl);
                    if (oldInstance) oldInstance.dispose();
                    modalEl.remove();
                }

                const html = `
                    <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.2); background: white !important;">
                                <div class="modal-header" style="border-bottom: none; padding: 25px 25px 10px; background: transparent !important;">
                                    <h4 class="modal-title fw-bold" style="color: #111827; margin: 0;">${title}</h4>
                                </div>
                                <div class="modal-body" style="padding: 10px 25px 25px; color: #4b5563; font-size: 1rem; background: transparent !important;">
                                    ${message}
                                </div>
                                <div class="modal-footer" style="border-top: none; padding: 0 25px 25px; gap: 10px; background: transparent !important;">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px; padding: 10px 20px; font-weight: 600; background: #f3f4f6; border: none; color: #374151;">Cancelar</button>
                                    <button type="button" id="${modalId}-confirm" class="btn btn-danger" style="border-radius: 10px; padding: 10px 20px; font-weight: 600; background: #ef4444; border: none; color: white;">Confirmar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', html);
                modalEl = document.getElementById(modalId);
                
                if (typeof bootstrap === 'undefined') {
                    console.error("Bootstrap is not defined! Falling back to native confirm.");
                    const res = confirm(message);
                    modalEl.remove();
                    resolve(res);
                    return;
                }

                const bsModal = new bootstrap.Modal(modalEl);
                
                document.getElementById(`${modalId}-confirm`).onclick = () => {
                    console.log("EducattioUI: Confirm clicked");
                    bsModal.hide();
                    resolve(true);
                };

                modalEl.addEventListener('hidden.bs.modal', () => {
                    console.log("EducattioUI: Modal hidden");
                    modalEl.remove();
                    resolve(false);
                }, { once: true });

                bsModal.show();
            });
        }
    };
    window.EducattioUI = EducattioUI;
})(window);
