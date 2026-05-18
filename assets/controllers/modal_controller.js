import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { open: Boolean };

    connect() {
        this.handleNativeClose = this.handleNativeClose.bind(this);
        this.handleBackdropClick = this.handleBackdropClick.bind(this);
        this.element.addEventListener('close', this.handleNativeClose);
        this.element.addEventListener('click', this.handleBackdropClick);
        this.syncOpenState();
    }

    disconnect() {
        this.element.removeEventListener('close', this.handleNativeClose);
        this.element.removeEventListener('click', this.handleBackdropClick);
    }

    openValueChanged() {
        this.syncOpenState();
    }

    syncOpenState() {
        if (this.openValue && !this.element.open) {
            this.element.showModal();
            return;
        }
        if (!this.openValue && this.element.open) {
            this.element.close();
        }
    }

    handleNativeClose() {
        if (this.openValue) {
            this.dispatch('close');
        }
    }

    handleBackdropClick(event) {
        if (event.target === this.element) {
            this.element.close();
        }
    }
}
