import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button'];

    open() {
        this.buttonTarget.setAttribute('aria-expanded', 'true');
        this.element.classList.add('is-open');
        document.body.classList.add('has-drawer-open');
    }

    close() {
        this.buttonTarget.setAttribute('aria-expanded', 'false');
        this.element.classList.remove('is-open');
        document.body.classList.remove('has-drawer-open');
    }

    toggle() {
        if (this.element.classList.contains('is-open')) {
            this.close();
        } else {
            this.open();
        }
    }

    closeOnLinkClick(event) {
        if (event.target.tagName === 'A') {
            this.close();
        }
    }
}
