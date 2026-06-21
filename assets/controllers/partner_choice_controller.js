import { Controller } from '@hotwired/stimulus';

// Tarot à 5 : le Partenaire doit être un Joueur actif distinct du Preneur.
// On retire en temps réel des choix Partenaire le Preneur sélectionné et les
// Morts désignés. Le choix « Preneur seul » (valeur vide) reste toujours offert.
// Preneur et Partenaire sont des segmented controls : chaque choix est un radio
// enveloppé dans un label « pill ». Masquer un choix = masquer son pill et
// désactiver son radio pour qu'il ne soit pas soumis.
export default class extends Controller {
    connect() {
        this.takerInputs = this.fieldInputs('[takerId]');
        this.partnerInputs = this.fieldInputs('[partnerId]');
        this.deadInputs = this.fieldInputs('[deadPlayerIds][]');

        if (this.takerInputs.length === 0 || this.partnerInputs.length === 0) {
            return;
        }

        [...this.takerInputs, ...this.deadInputs].forEach((input) => {
            input.addEventListener('change', () => this.refresh());
        });

        this.refresh();
    }

    refresh() {
        const excludedIds = this.excludedIds();

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

    excludedIds() {
        const ids = [];

        const taker = this.takerInputs.find((input) => input.checked);
        if (taker !== undefined) {
            ids.push(taker.value);
        }

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
