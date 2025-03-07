import Sortable from '@stimulus-components/sortable';

export default class extends Sortable {

    async onUpdate({ item, newIndex }) {
        if (!item.dataset.sortableUpdateUrl) {
            return;
        }

        const param = this.resourceNameValue ? `${this.resourceNameValue}[${this.paramNameValue}]` : this.paramNameValue

        const data = new FormData()
        data.append(param, newIndex + 1)

        return fetch(item.dataset.sortableUpdateUrl, {
            method: 'POST',
            body: data,
        })
    }

}
