import { Controller } from '@hotwired/stimulus';
import { get } from 'util';

export default class extends Controller {

  static values = {
    id: String,
    url: String
  }

  connect() {
    if (!this.idValue) {
      throw new Error('Issue id value must be set');
    }
  }

  async update({ detail: data }) {
    if (data.issueId !== this.idValue) {
      return;
    }

    this.element.outerHTML = await get(this.urlValue);
  }
}
