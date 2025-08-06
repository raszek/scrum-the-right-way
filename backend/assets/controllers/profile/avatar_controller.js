import {Controller} from '@hotwired/stimulus';
import Cropper from 'cropperjs';
import {Modal} from 'bootstrap';
import {dataURItoBlob, post} from 'util';

export default class extends Controller {

    static targets = [
        'text',
        'cropper',
        'fileInput',
        'dropzone',
        'modal',
        'loader',
        'image',
        'removeButton'
    ];

    static values = {
        url: String,
        imageUrl: String,
        uploaded: Boolean,
    };

    connect() {
        this.dropzoneTarget.addEventListener('drop', this.cropImage.bind(this));
        this.dropzoneTarget.addEventListener('dragover', this.dragover.bind(this));

        this.fileInputTarget.addEventListener('change', this.changeFile.bind(this));

        this.cropper = new Cropper(this.cropperTarget, {
            template: this.cropperTemplate(),
        });
        this.modal = new Modal(this.modalTarget);
    }

    async upload(event) {
        event.preventDefault();

        const canvas = await this.cropper.getCropperSelection().$toCanvas();

        const dataURL = canvas.toDataURL();
        const blob = dataURItoBlob(dataURL, 'image/png');

        const fileToSend = new File([blob], 'avatar.png', {
            type: 'image/png',
        });

        const formData = new FormData();
        formData.append('avatar', fileToSend);

        this.avatarUploading();
        this.dropzoneTarget.innerHTML = await post(this.urlValue, formData);

        this.modal.hide();
    }

    async removeAvatar(event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append('file', null);

        this.avatarUploading();
        this.dropzoneTarget.innerHTML = await post(this.urlValue, formData);
    }

    avatarUploading() {
        this.showLoader();
        this.hideText();
        this.hideImage();
        this.hideRemoveButton();
    }

    hideRemoveButton() {
        this.removeButtonTarget.classList.add('d-none');
    }

    hideImage() {
        this.imageTarget.classList.add('d-none');
    }

    showLoader() {
        this.loaderTarget.classList.remove('d-none');
    }

    hideText() {
        this.textTarget.classList.add('d-none');
    }

    showFilePicker() {
        this.fileInputTarget.click();
    }

    changeFile(event) {
        this.cropImage(event.target.files[0]);
    }

    dragover(event) {
        event.preventDefault();
    }

    dropFile(event) {
        event.preventDefault();

        const files = event.dataTransfer.files;

        if (files.length < 1) {
            return;
        }

        this.cropImage(files[0]);
    }

    cropImage(file) {
        if (!file.type.match('image/jpeg|image/png')) {
            this.textTarget.textContent = 'Please upload a JPG or PNG image file';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            this.textTarget.textContent = 'File size should not exceed 10MB';
            return;
        }

        const reader = new FileReader();
        reader.onload = this.showCropper.bind(this);
        reader.readAsDataURL(file);
    }

    showCropper(e) {
        console.log('dziala');
        this.cropper.getCropperImage().src = e.target.result;

        this.modal.show();
    }

    cropperTemplate() {
        return `
        <cropper-canvas 
            background 
            style="height: 400px;"
        >
          <cropper-image src="" alt="Picture" rotatable scalable skewable translatable>
          </cropper-image>
          <cropper-selection width="200" height="200" movable>
            <cropper-handle action="move"></cropper-handle>
          </cropper-selection>
        </cropper-canvas>
        `;
    }
}
