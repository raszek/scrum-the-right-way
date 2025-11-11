import WidgetController from '../widget_controller.js';

export default class TodoController extends WidgetController {

    static targets = ['input'];

    add(event) {
        event.preventDefault();

        this.addItem(this.inputTarget.value);

        this.inputTarget.value = '';
    }

    addItem(task) {
        const addedItem = {
            task,
            isDone: false,
        };

        this.attributesValue = {
            items: this.attributesValue.items.concat(addedItem),
        };
    }


}
