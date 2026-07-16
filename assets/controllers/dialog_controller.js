import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dialog'];

    connect() {
        this.handleBackdropClick = this.handleBackdropClick.bind(this);
        this.dialogTarget.addEventListener('click', this.handleBackdropClick);
    }

    disconnect() {
        this.dialogTarget.removeEventListener('click', this.handleBackdropClick);
    }

    open() {
        this.dialogTarget.showModal();
    }

    close() {
        this.dialogTarget.close();
    }

    handleBackdropClick(event) {
        if (event.target === this.dialogTarget) {
            this.dialogTarget.close();
        }
    }
}
