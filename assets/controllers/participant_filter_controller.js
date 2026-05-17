import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'option', 'counter', 'empty'];
    static values = {
        counterZero: String,
        counterOne: String,
        counterMany: String,
    };

    connect() {
        this.optionTargets.forEach((option) => {
            const checkbox = option.querySelector('input[type="checkbox"]');
            checkbox.addEventListener('change', () => this.updateCounter());

            const label = option.querySelector('span')?.textContent ?? '';
            option.dataset.normalizedName = this.normalize(label);
        });

        this.updateCounter();
        this.applyFilter();
    }

    filter() {
        this.applyFilter();
    }

    applyFilter() {
        const term = this.normalize(this.inputTarget.value);
        let visibleCount = 0;

        this.optionTargets.forEach((option) => {
            const checkbox = option.querySelector('input[type="checkbox"]');
            const isChecked = checkbox.checked;
            const matches = term === '' || option.dataset.normalizedName.includes(term);
            const visible = isChecked || matches;

            option.hidden = !visible;
            if (visible) {
                visibleCount += 1;
            }
        });

        this.emptyTarget.hidden = visibleCount > 0;
    }

    updateCounter() {
        const checkedCount = this.optionTargets.filter(
            (option) => option.querySelector('input[type="checkbox"]').checked,
        ).length;

        let text;
        if (checkedCount === 0) {
            text = this.counterZeroValue;
        } else if (checkedCount === 1) {
            text = this.counterOneValue;
        } else {
            text = this.counterManyValue.replace('{count}', String(checkedCount));
        }

        this.counterTarget.textContent = text;
    }

    normalize(value) {
        return value
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .trim();
    }
}
