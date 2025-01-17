import { Controller } from '@hotwired/stimulus';
import {post, randomString} from 'util';

export default class extends Controller {

    static targets = ['columnTemplate', 'item', 'dropzone', 'emptyMessage', 'fileInput'];

    static values = {
        url: String,
        canUpload: String
    }

    connect() {
        this.dropzoneTarget.ondragleave = this.dragLeave.bind(this);
        this.dropzoneTarget.ondragover = this.dragOver.bind(this);
        this.dropzoneTarget.ondrop = this.uploadFiles.bind(this);
        this.fileInputTarget.onchange = this.windowUploadFiles.bind(this);

        this.showEmptyMessageOnNeed();
    }

    async showFilePicker() {
        if (!this.canUserUpload()) {
            return;
        }

        this.fileInputTarget.click();
    }

    canUserUpload() {
        return this.canUploadValue === 'true';
    }

    windowUploadFiles(e) {
        const promises = [];
        for (const file of e.target.files) {
            promises.push(this.uploadFile(file));
        }

        return Promise.all(promises);
    }

    uploadFiles(e) {
        e.preventDefault();
        this.removeOpacity();

        if (!this.canUserUpload()) {
            return;
        }

        if (!e.dataTransfer.items) {
            return;
        }

        [...e.dataTransfer.items].forEach((item, i) => {
            if (item.kind !== 'file') {
                return;
            }

            this.uploadFile(item.getAsFile());
        });
    }

    findItem(itemId) {
        for (const itemTarget of this.itemTargets) {
            if (itemTarget.getAttribute('data-dropzone-item-id-param') === itemId) {
                return itemTarget;
            }
        }

        return undefined;
    }

    stopPropagation(e) {
        e.stopPropagation();
    }

    async uploadFile(file) {
        const itemId = randomString(6);

        const clone = this.columnTemplateTarget.content.cloneNode(true);

        clone.firstElementChild.setAttribute('data-dropzone-item-id-param', itemId);
        clone.firstElementChild.setAttribute('data-dropzone-target', 'item');

        this.dropzoneTarget.append(clone);

        const formData = new FormData();
        formData.append('file', file);

        const item = this.findItem(itemId);

        try {
            item.outerHTML = await post(this.urlValue, formData);
        } catch (e) {
            item.firstChildElement.textContent = 'Error';
        }

        this.showEmptyMessageOnNeed();
    }

    removeFile(e) {
        e.stopPropagation();

        const itemId = String(e.params.itemId);

        const item = this.findItem(itemId);

        const removeUrl = item.getAttribute('data-dropzone-item-remove-url-param');

        item.remove();

        this.showEmptyMessageOnNeed();

        if (!removeUrl) {
            return;
        }

        return post(removeUrl);
    }

    hasItems() {
        return this.itemTargets.length > 0;
    }

    showEmptyMessageOnNeed() {
        if (this.hasItems()) {
            this.hideEmptyMessage();
        } else {
            this.showEmptyMessage();
        }
    }

    showEmptyMessage() {
        this.emptyMessageTarget.style.display = 'flex';
    }

    hideEmptyMessage() {
        this.emptyMessageTarget.style.display = 'none';
    }

    dragOver(e) {
        e.preventDefault();
        this.addOpacity();
    }

    dragLeave(e) {
        e.preventDefault();
        this.removeOpacity();
    }

    addOpacity() {
        this.element.style.opacity = 0.6;
    }
    removeOpacity() {
        this.element.style.opacity = 1;
    }
}
