import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.takerInputs = this.fieldInputs('[takerId]');
        this.partnerInputs = this.fieldInputs('[partnerId]');
        this.deadInputs = this.fieldInputs('[deadPlayerIds][]');

        if (this.takerInputs.length === 0) {
            return;
        }

        [...this.takerInputs, ...this.deadInputs].forEach((input) => {
            input.addEventListener('change', () => this.refresh());
        });

        this.refresh();
    }

    refresh() {
        this.refreshTaker();
        this.refreshPartner();
    }

    refreshTaker() {
        const deadIds = this.deadIds();

        this.takerInputs.forEach((input) => {
            const isDead = deadIds.includes(input.value);
            this.setChoiceHidden(input, isDead);

            if (isDead && input.checked) {
                input.checked = false;
            }
        });
    }

    refreshPartner() {
        if (this.partnerInputs.length === 0) {
            return;
        }

        const excludedIds = this.deadIds();
        const taker = this.takerInputs.find((input) => input.checked);
        if (taker !== undefined) {
            excludedIds.push(taker.value);
        }

        this.partnerInputs.forEach((input) => {
            if (input.value === '') {
                return;
            }

            const isExcluded = excludedIds.includes(input.value);
            this.setChoiceHidden(input, isExcluded);

            if (isExcluded && input.checked) {
                this.selectAlone();
            }
        });
    }

    deadIds() {
        const ids = [];

        this.deadInputs.forEach((input) => {
            if (input.checked) {
                ids.push(input.value);
            }
        });

        return ids;
    }

    setChoiceHidden(input, hidden) {
        input.disabled = hidden;

        const pill = input.closest('.ab-segmented__option');
        if (pill !== null) {
            pill.hidden = hidden;
        }
    }

    selectAlone() {
        const alone = this.partnerInputs.find((input) => input.value === '');
        if (alone !== undefined) {
            alone.checked = true;
        }
    }

    fieldInputs(suffix) {
        return Array.from(this.element.querySelectorAll(`[name$="${suffix}"]`));
    }
}
