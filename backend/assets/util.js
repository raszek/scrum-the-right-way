export function defaultDateFormat () {
    return 'dd.MM.yyyy';
}

const characters ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

export function randomString(length) {
    let result = '';
    const charactersLength = characters.length;
    for ( let i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }

    return result;
}

export function isEmpty(obj) {
    return Object.keys(obj).length === 0;
}

export class Color
{
    constructor(red, green, blue, opacity = 0) {
        this.red = red;
        this.green = green;
        this.blue = blue;
        this.opacity = opacity;
    }

    formatHex() {
        return `#${this.componentToHex(this.red)}${this.componentToHex(this.green)}${this.componentToHex(this.blue)}`;
    }

    componentToHex(colorInteger) {
        const hex = colorInteger.toString(16);

        return hex.length === 1 ? '0' + hex : hex;
    }
}

export function isArrayEqual(arr1, arr2) {
    if (arr1.length !== arr2.length) {
        return false;
    }

    for (let i = 0; i < arr1.length; i++) {
        if (arr1[i] !== arr2[i]) {
            return false;
        }
    }

    return true;
}


export function randomColor() {
    return new Color(
        randomInteger(0, 255),
        randomInteger(0, 255),
        randomInteger(0, 255),
    );
}

export function dataURItoBlob(dataURI, mime) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(',')[0].indexOf('base64') >= 0)
        byteString = atob(dataURI.split(',')[1]);
    else
        byteString = unescape(dataURI.split(',')[1]);

    // write the bytes of the string to a typed array
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }

    return new Blob([ia], {type: mime});
}

export function textColorBasedOnBackground(backgroundColor) {
    backgroundColor = backgroundColor.substring(1);
    const r = parseInt(backgroundColor.substring(0,2), 16); // 0 ~ 255
    const g = parseInt(backgroundColor.substring(2,4), 16);
    const b = parseInt(backgroundColor.substring(4,6), 16);

    const srgb = [r / 255, g / 255, b / 255];
    const x = srgb.map((i) => {
        if (i <= 0.04045) {
            return i / 12.92;
        } else {
            return Math.pow((i + 0.055) / 1.055, 2.4);
        }
    });

    const L = 0.2126 * x[0] + 0.7152 * x[1] + 0.0722 * x[2];
    return L > 0.179 ? '#000' : '#fff';
}

export function randomInteger(min, max) {
    const minCeiled = Math.ceil(min);
    const maxFloored = Math.floor(max);
    return Math.floor(Math.random() * (maxFloored - minCeiled) + minCeiled);
}

export function randomElement(items) {
    return items[Math.floor(Math.random() * items.length)];
}

export const get = async (url) => {
    try {
        const result = await fetch(url);

        return result.text();
    } catch (e) {
        console.log(e);
    }
};

/**
 * @param {string} url
 * @param {FormData | undefined} data
 * @returns {Promise<string>}
 */
export const post = async (url, data = undefined) => {
    try {
        const result = await fetch(url, {
            method: 'POST',
            body: data
        });

        return result.text();
    } catch (e) {
        console.log(e);
    }
};
