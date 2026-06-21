import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.selects = Array.from(
            this.element.querySelectorAll('select[name*="[ranking]"]'),
        );
        this.deadInputs = Array.from(
            this.element.querySelectorAll('[name*="[deadPlayerIds]"]'),
        );

        if (this.selects.length === 0) {
            return;
        }

        [...this.selects, ...this.deadInputs].forEach((input) => {
            input.addEventListener('change', () => this.refresh());
        });

        this.refresh();
    }

    refresh() {
        const owners = this.ownersByPlayerId();

        this.selects.forEach((select) => {
            Array.from(select.options).forEach((option) => {
                if (option.value === '') {
                    return;
                }

                const owner = owners.get(option.value);
                const takenByAnother = owner !== undefined && owner !== select;
                option.hidden = takenByAnother;
                option.disabled = takenByAnother;
            });
        });
    }

    ownersByPlayerId() {
        const owners = new Map();

        this.selects.forEach((select) => {
            if (select.value !== '') {
                owners.set(select.value, select);
            }
        });

        this.deadInputs.forEach((input) => {
            if (input.checked) {
                owners.set(input.value, input);
            }
        });

        return owners;
    }
}
