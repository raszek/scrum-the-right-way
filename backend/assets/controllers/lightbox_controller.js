import { Controller } from '@hotwired/stimulus';
import GLightBox from 'glightbox';

export default class extends Controller {

    static targets = ['item'];

    openGallery(e) {
        e.stopPropagation();

        const startAt = this.getStartIndex(String(e.params.id));

        const gallery = GLightBox({
            elements: this.getImages(),
            startAt: startAt || 0
        });

        gallery.open();
    }

    getStartIndex(id) {
        const images = this.itemTargets;

        for (let i = 0; i < images.length; i++) {
            if (images[i].getAttribute('data-lightbox-id-param') === id) {
                return i;
            }
        }

        return undefined;
    }

    getImages() {
        return this.itemTargets.map((itemTarget) => ({
            href: itemTarget.getAttribute('src'),
            type: this.isVideo(itemTarget.getAttribute('data-lightbox-extension-param')) ? 'video' : 'image'
        }));
    }

    isVideo(extension) {
        return ['mp4'].includes(extension);
    }
}
