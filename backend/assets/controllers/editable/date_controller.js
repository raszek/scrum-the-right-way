import {format} from 'date-fns';
import {defaultDateFormat} from 'util';

import EditableController from './editable_controller.js';

export default class extends EditableController {

    formatValue(value) {
        return format(new Date(value), defaultDateFormat());
    }

}
