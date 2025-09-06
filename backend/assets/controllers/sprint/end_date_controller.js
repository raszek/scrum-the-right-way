import DateController from '../editable/date_controller.js';

export default class extends DateController {

    afterRequestAction(value) {
        this.dispatch('modified', {
            detail: {
                content: value
            }
        });
    }
}
