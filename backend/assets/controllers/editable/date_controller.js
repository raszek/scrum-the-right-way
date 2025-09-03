import EditableController from './editable_controller.js';
import {DateTime} from 'util';

export default class extends EditableController {

    formatValue(value) {
        const dateTime = new DateTime(value);

        return dateTime.defaultFormat();
    }

}
