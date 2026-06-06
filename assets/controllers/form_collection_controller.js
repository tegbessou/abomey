import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['list', 'modal', 'field'];
    static values = {
        index: Number,
        namePrefix: String,
        removeLabel: { type: String, default: 'Retirer' },
    };

    connect() {
        if (this.indexValue === 0) {
            this.indexValue = this.listTarget.children.length;
        }
    }

    openModal(event) {
        event.preventDefault();
        this.modalTarget.showModal();
    }

    closeModal(event) {
        event.preventDefault();
        this.modalTarget.close();
    }

    confirmAdd(event) {
        event.preventDefault();

        const labels = [];
        const hiddenInputs = [];

        this.fieldTargets.forEach((field) => {
            const fieldName = field.dataset.fieldName;
            const value = field.value;
            let label = value;
            if (field.tagName === 'SELECT' && field.selectedOptions.length > 0) {
                label = field.selectedOptions[0].textContent.trim();
            }
            labels.push(label);

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `${this.namePrefixValue}[${this.indexValue}][${fieldName}]`;
            hidden.value = value;
            hiddenInputs.push(hidden);
        });

        const item = document.createElement('li');
        item.classList.add('form-collection-line');

        const labelSpan = document.createElement('span');
        labelSpan.classList.add('form-collection-line__label');
        labelSpan.textContent = labels.join(' — ');
        item.appendChild(labelSpan);

        hiddenInputs.forEach((hidden) => item.appendChild(hidden));

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.classList.add('form-collection-remove');
        removeButton.textContent = '×';
        removeButton.dataset.action = 'click->form-collection#remove';
        removeButton.setAttribute('aria-label', this.removeLabelValue);
        item.appendChild(removeButton);

        this.listTarget.appendChild(item);
        this.indexValue++;
        this.modalTarget.close();
    }

    remove(event) {
        event.preventDefault();
        event.target.closest('.form-collection-line').remove();
    }
}
