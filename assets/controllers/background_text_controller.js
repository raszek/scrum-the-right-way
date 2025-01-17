import { Controller } from '@hotwired/stimulus';
import {textColorBasedOnBackground} from 'util';

export default class extends Controller {

    static values = {
        backgroundColor: String
    }

    connect() {
        super.connect();

        this.element.style.backgroundColor = this.backgroundColorValue;
        this.element.style.color = textColorBasedOnBackground(this.backgroundColorValue);
    }
}
