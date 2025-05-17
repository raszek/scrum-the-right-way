import { Controller } from '@hotwired/stimulus';
import {Modal} from 'bootstrap';
import {post} from 'util';

export default class extends Controller {

    static targets = ['goal', 'modal'];

    connect() {
        this.modal = new Modal(this.modalTarget);
    }

    showModal(event) {
        event.preventDefault();

        this.modal.show();

        const goalId = event.params.id;

        const goal = this.findGoal(goalId);

        if (!goal) {
            throw new Error('Goal not found');
        }

        const goalName = goal.innerText.trim();

        const form = this.modalTarget.querySelector('form');

        if (!form) {
            throw new Error('Modal form not found');
        }

        form.setAttribute('action', event.params.url);
        const nameField = form.querySelector('textarea[name="name"]');
        if (!nameField) {
            throw new Error('Name field not found');
        }

        nameField.value = goalName;

        const idField = form.querySelector('input[name="id"]');
        if (!idField) {
            throw new Error('Id field not found');
        }

        idField.value = goalId;
    }

    submit(event) {
        event.preventDefault();

        const formData = new FormData(event.currentTarget);

        this.editGoalName(formData.get('name'), formData.get('id'));

        this.modal.hide();

        const url = event.currentTarget.getAttribute('action');

        return post(url, formData);
    }

    editGoalName(name, goalId) {
        const goal = this.findGoal(goalId);

        if (!goal) {
            throw new Error('Goal not found');
        }

        goal.innerText = name;
    }

    findGoal(goalId) {
        for (const goalTarget of this.goalTargets) {
            if (goalTarget.getAttribute('data-sprint--edit-goal-id-param') === goalId) {
                return goalTarget;
            }
        }

        return undefined;
    }

}
