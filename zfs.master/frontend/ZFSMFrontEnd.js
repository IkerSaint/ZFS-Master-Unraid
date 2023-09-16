//region utils

var zfs_logo = '<svg width="100%" height="100%" version="1.1" viewBox="0 0 358 326" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' +
			   '<title>OpenZFS logo</title><metadata><rdf:rdf><cc:work rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"></dc:type>' +
			   '<dc:title></dc:title></cc:work></rdf:rdf></metadata><g transform="matrix(.989 0 0 .993 546 -255)"><g transform="matrix(1.25 0 0 -1.25 -388 570)">'+
			   '<path d="m0 0v35.1h3.68c0.877 0 1.43-0.507 1.66-1.52l0.484-4.52c1.53 1.81 3.23 3.28 5.11 4.39s4.06 1.68 6.53 1.68c1.92 0 3.61-0.314 5.08-0.94 1.47-0.625 2.69-1.51 3.67-2.66s1.73-2.53 2.24-4.15 0.762-3.4 0.762-5.36v-22h-6.17v22c0 2.61-0.606 4.64-1.82 6.08s-3.07 2.16-5.56 2.16c-1.83 0-3.53-0.465-5.11-1.4-1.58-0.93-3.04-2.19-4.38-3.78v-25zm-21.1 30.6c-2.98 0-5.33-0.774-7.04-2.32-1.71-1.55-2.77-3.7-3.19-6.44h19.1c0 1.29-0.197 2.47-0.589 3.54-0.391 1.07-0.971 2-1.73 2.78s-1.69 1.38-2.79 1.81c-1.1 0.427-2.35 0.64-3.76 0.64m-0.138 4.54c2.1 0 4.04-0.333 5.82-1 1.78-0.667 3.32-1.63 4.61-2.89 1.29-1.26 2.3-2.81 3.03-4.66 0.728-1.85 1.09-3.95 1.09-6.32 0-0.918-0.104-1.53-0.312-1.84-0.208-0.307-0.602-0.459-1.18-0.459h-23.4c0.047-2.22 0.346-4.15 0.901-5.79 0.555-1.64 1.32-3.01 2.29-4.11 0.97-1.1 2.13-1.92 3.47-2.46s2.84-0.814 4.51-0.814c1.55 0 2.88 0.179 4 0.537 1.12 0.358 2.08 0.745 2.89 1.16 0.809 0.415 1.49 0.802 2.03 1.16 0.543 0.357 1.01 0.537 1.4 0.537 0.508 0 0.902-0.197 1.18-0.59l1.73-2.25c-0.762-0.924-1.68-1.73-2.74-2.41-1.06-0.682-2.2-1.24-3.41-1.68-1.21-0.439-2.47-0.769-3.76-0.989-1.29-0.219-2.58-0.329-3.85-0.329-2.43 0-4.66 0.399-6.71 1.2-2.04 0.798-3.81 1.97-5.3 3.51-1.49 1.54-2.65 3.45-3.48 5.72-0.833 2.27-1.25 4.88-1.25 7.82 0 2.38 0.375 4.61 1.12 6.68 0.752 2.07 1.83 3.86 3.24 5.38 1.41 1.52 3.13 2.71 5.16 3.58 2.03 0.866 4.32 1.3 6.86 1.3m-36.4-4.99c-2.01 0-3.77-0.459-5.28-1.37-1.51-0.916-2.91-2.21-4.18-3.88v-16.2c1.13-1.45 2.37-2.48 3.72-3.07s2.86-0.892 4.52-0.892c3.26 0 5.77 1.12 7.52 3.36 1.76 2.24 2.63 5.44 2.63 9.59 0 2.2-0.203 4.08-0.605 5.66-0.406 1.58-0.989 2.87-1.75 3.88-0.763 1.01-1.7 1.75-2.81 2.21-1.11 0.467-2.37 0.7-3.78 0.7m-15.6-42v47h3.68c0.877 0 1.43-0.486 1.66-1.46l0.521-4.72c1.5 1.87 3.22 3.38 5.15 4.51 1.93 1.14 4.15 1.7 6.67 1.7 2.01 0 3.84-0.376 5.48-1.13 1.64-0.752 3.04-1.86 4.2-3.33 1.15-1.47 2.04-3.29 2.67-5.47 0.624-2.18 0.936-4.68 0.936-7.51 0-2.51-0.346-4.85-1.04-7.02s-1.69-4.04-2.98-5.62c-1.29-1.58-2.88-2.83-4.77-3.74s-4-1.36-6.36-1.36c-2.17 0-4.03 0.358-5.56 1.08-1.54 0.715-2.89 1.73-4.07 3.05v-16zm-13.3 36.7c0 2.97-0.416 5.64-1.25 8-0.832 2.36-2.01 4.36-3.54 6s-3.38 2.89-5.55 3.76c-2.17 0.878-4.6 1.32-7.28 1.32-2.66 0-5.07-0.439-7.24-1.32-2.17-0.878-4.03-2.13-5.56-3.76s-2.72-3.63-3.55-6c-0.832-2.36-1.25-5.03-1.25-8 0-2.97 0.416-5.63 1.25-7.99 0.832-2.35 2.02-4.35 3.55-5.98s3.39-2.88 5.56-3.75c2.17-0.867 4.59-1.3 7.24-1.3 2.68 0 5.11 0.433 7.28 1.3 2.17 0.867 4.02 2.12 5.55 3.75s2.7 3.62 3.54 5.98c0.831 2.35 1.25 5.01 1.25 7.99m6.9 0c0-3.65-0.59-6.99-1.77-10-1.18-3.05-2.84-5.67-4.99-7.86-2.15-2.2-4.73-3.9-7.75-5.11-3.02-1.21-6.35-1.82-10-1.82-3.65 0-6.98 0.606-9.98 1.82-3 1.21-5.58 2.92-7.73 5.11-2.15 2.2-3.81 4.82-4.99 7.86-1.18 3.05-1.77 6.39-1.77 10 0 3.65 0.588 6.99 1.77 10 1.18 3.05 2.84 5.67 4.99 7.88 2.15 2.21 4.72 3.92 7.73 5.15 3 1.22 6.33 1.84 9.98 1.84 3.65 0 6.98-0.613 10-1.84 3.02-1.22 5.6-2.94 7.75-5.15 2.15-2.21 3.81-4.84 4.99-7.88 1.18-3.04 1.77-6.39 1.77-10" fill="#2e4349" class="svg-elem-1"></path>'+
			   '</g><g transform="matrix(1.25 0 0 -1.25 -211 518)">'+
			   '<path d="m0 0c-2.2-6e-3 -3.83-0.456-4.9-1.37-1.08-0.913-1.61-2.15-1.61-3.7 0-0.993 0.324-1.82 0.971-2.47 0.646-0.654 1.5-1.22 2.55-1.69 1.05-0.474 2.25-0.908 3.6-1.3 1.35-0.395 2.73-0.834 4.14-1.32 1.41-0.484 2.79-1.05 4.14-1.71s2.55-1.49 3.6-2.5c1.05-1.01 1.9-2.25 2.55-3.7 0.646-1.45 0.971-3.21 0.971-5.26 0-2.28-0.405-4.41-1.21-6.39-0.808-1.98-1.98-3.71-3.52-5.19-1.54-1.48-3.43-2.64-5.69-3.48-2.25-0.846-4.81-1.27-7.68-1.27-1.57 0-3.17 0.162-4.8 0.485-1.63 0.324-3.21 0.78-4.73 1.37-1.53 0.59-2.96 1.29-4.3 2.1s-2.51 1.71-3.5 2.7l3.46 5.37c0.255 0.407 0.613 0.736 1.07 0.985 0.462 0.249 0.96 0.375 1.49 0.375 0.693 0 1.39-0.213 2.1-0.638 0.706-0.425 1.5-0.896 2.39-1.41 0.891-0.516 1.91-0.986 3.07-1.41 1.16-0.425 2.52-0.637 4.09-0.637 2.12 0 3.78 0.456 4.96 1.37 1.18 0.912 1.77 2.36 1.77 4.34 0 1.15-0.325 2.08-0.971 2.81-0.648 0.721-1.5 1.32-2.55 1.79-1.05 0.474-2.25 0.891-3.59 1.25-1.34 0.361-2.71 0.76-4.12 1.2-1.41 0.44-2.79 0.98-4.12 1.62-1.34 0.643-2.54 1.49-3.59 2.54-1.05 1.05-1.9 2.36-2.55 3.92-0.646 1.57-0.97 3.5-0.97 5.8 0 1.85 0.381 3.65 1.14 5.41 0.763 1.76 1.88 3.32 3.36 4.7 1.48 1.37 3.29 2.47 5.44 3.3 2.15 0.822 4.61 1.23 7.38 1.23 1.55 0 3.06-0.121 4.52-0.364 1.47-0.242 2.86-0.601 4.18-1.08 1.32-0.473 2.55-1.04 3.69-1.7 1.14-0.658 2.17-1.4 3.07-2.24 0 0-1.38-2.64-2.85-5.39-0.624-1.17-2-2.18-3.66-1.4-1.67 0.779-4.83 2.96-8.81 2.95m-21.8 9.18v-9.01h-20.7v-12.8h17.3v-9.05h-17.3v-19.7h-11.8v50.5zm-40.1 0v-4.23c0-0.601-0.098-1.19-0.293-1.77-0.197-0.578-0.467-1.12-0.814-1.63l-23.7-33.9h24.1v-9.01h-39v4.51c0 0.531 0.091 1.06 0.275 1.58 0.186 0.519 0.44 0.999 0.762 1.44l23.8 34h-22.9v9.01z" fill="#2a667f" class="svg-elem-2"></path>'+
			   '</g>'+
			   '<path d="m-308 288c-2.12 2.13-5.56 2.13-7.68 0-2.12-2.12-2.12-5.57 0-7.69 2.12-2.13 5.56-2.13 7.68 0 2.13 2.12 2.13 5.57-2e-3 7.69m-1.19 152c-3.71 3.71-9.72 3.71-13.4 0-3.71-3.71-3.71-9.72 0-13.4 3.71-3.71 9.73-3.71 13.4 0 3.71 3.71 3.71 9.72 0 13.4m-31.7-18.3c-3.71-3.71-3.71-9.73 0-13.4 3.71-3.71 9.73-3.71 13.4 0 3.72 3.71 3.71 9.73 2e-3 13.4-3.71 3.71-9.73 3.71-13.4 0m-4.87 18.3c-3.71 3.71-9.73 3.71-13.4 0-3.72-3.71-3.72-9.72 0-13.4 3.71-3.71 9.72-3.71 13.4 0 3.71 3.71 3.71 9.72 0 13.4m-31.7-18.3c-3.71-3.71-3.71-9.73 0-13.4 3.71-3.71 9.73-3.71 13.4 2e-3 3.71 3.71 3.71 9.73 0 13.4-3.71 3.71-9.73 3.71-13.4 0m-4.87 18.3c-3.71 3.71-9.73 3.71-13.4-4e-3 -3.71-3.72-3.71-9.72 0-13.4 3.71-3.71 9.73-3.71 13.4 0 3.71 3.71 3.72 9.72-1e-3 13.4m-31.7-18.3c-3.71-3.71-3.71-9.72-1e-3 -13.4 3.72-3.71 9.73-3.71 13.4 0 3.71 3.71 3.71 9.73 0 13.4-3.7 3.7-9.72 3.71-13.4-7e-3m-4.87 18.3c-3.71 3.71-9.73 3.71-13.4 0-3.71-3.72-3.71-9.73 0-13.4 3.71-3.71 9.72-3.71 13.4 0 3.71 3.71 3.71 9.73 0 13.4m-14.6-159c2.13-2.13 5.56-2.13 7.69 0 2.12 2.12 2.12 5.57 0 7.69-2.13 2.13-5.57 2.13-7.69 0-2.12-2.12-2.13-5.57-2e-3 -7.69m11.8 11.8c2.12-2.12 5.56-2.12 7.69 2e-3 2.12 2.13 2.12 5.57 0 7.7-2.13 2.12-5.57 2.12-7.69 0-2.12-2.13-2.13-5.57 2e-3 -7.7m11.8 11.8c2.12-2.13 5.57-2.13 7.69 0 2.13 2.12 2.12 5.57-1e-3 7.69-2.12 2.13-5.56 2.13-7.68 0-2.13-2.12-2.13-5.57-5e-3 -7.69m5e-3 -23.6c2.12-2.13 5.56-2.13 7.68 2e-3 2.13 2.12 2.12 5.57 0 7.69-2.12 2.13-5.56 2.12-7.68 0-2.13-2.12-2.13-5.57 0-7.69m11.8 35.4c2.13-2.12 5.57-2.13 7.69 2e-3 2.13 2.12 2.13 5.57 0 7.7-2.12 2.12-5.56 2.12-7.69 0-2.12-2.13-2.12-5.58 0-7.7m5e-3 -23.6c2.12-2.12 5.56-2.12 7.68 2e-3 2.12 2.13 2.13 5.57 0 7.7-2.12 2.12-5.56 2.12-7.69 0-2.12-2.13-2.12-5.58 5e-3 -7.7m11.8-11.8c2.12-2.13 5.56-2.13 7.69 0 2.12 2.12 2.12 5.57 0 7.69-2.13 2.13-5.57 2.12-7.69 0-2.12-2.12-2.12-5.57 0-7.69m11.8 35.4c2.12-2.13 5.56-2.12 7.69-2e-3 2.12 2.13 2.12 5.58 0 7.7-2.12 2.12-5.56 2.12-7.69 0-2.12-2.13-2.13-5.57 0-7.7m0-23.6c2.12-2.12 5.56-2.12 7.69 0s2.12 5.57-2e-3 7.7c-2.12 2.12-5.57 2.12-7.69 0-2.12-2.13-2.12-5.58 0-7.7m11.8-11.8c2.13-2.13 5.57-2.13 7.69 0 2.12 2.12 2.12 5.57 2e-3 7.69-2.12 2.12-5.56 2.13-7.69 0-2.12-2.12-2.12-5.57 0-7.69m3.64 109c3.71-3.71 9.73-3.71 13.4 0 3.71 3.71 3.71 9.73 0 13.4-3.71 3.71-9.73 3.71-13.4 0-3.72-3.71-3.72-9.73 0-13.4m4.05-54.4c-2.12 2.13-5.56 2.12-7.69 0-2.12-2.13-2.12-5.57-2e-3 -7.7 2.12-2.12 5.57-2.12 7.69 0.01 2.12 2.12 2.12 5.57 0 7.69m0-23.6c-2.12 2.13-5.56 2.13-7.69 3e-3 -2.12-2.12-2.12-5.57 0-7.69 2.12-2.13 5.57-2.13 7.69 0 2.12 2.12 2.12 5.57 2e-3 7.69m-19.5 27.7c2.12-2.12 5.56-2.12 7.69 0 2.12 2.12 2.12 5.58-1e-3 7.7-2.12 2.12-5.56 2.12-7.69-1e-3 -2.12-2.13-2.12-5.57 7e-3 -7.7m-2.88 32c3.71-3.71 9.73-3.71 13.4 2e-3 3.71 3.71 3.71 9.72 0 13.4-3.71 3.71-9.73 3.71-13.4 2e-3 -3.71-3.71-3.71-9.72 0-13.4m-4.87 18.3c3.71 3.71 3.71 9.73 0 13.4-3.71 3.71-9.73 3.71-13.4-2e-3 -3.72-3.71-3.71-9.72 0-13.4 3.7-3.71 9.73-3.71 13.4 0m-4.05-85.7c2.12-2.13 5.56-2.13 7.69 0 2.12 2.12 2.12 5.57 0 7.69-2.13 2.13-5.56 2.13-7.69 3e-3 -2.12-2.12-2.12-5.57-1e-3 -7.69m0 23.6c2.12-2.13 5.56-2.13 7.69-7e-3 2.12 2.13 2.12 5.58 0 7.71-2.13 2.12-5.57 2.13-7.69-7e-3 -2.12-2.13-2.12-5.57 0-7.69m35.4-11.8c2.12-2.12 5.56-2.13 7.69 0 2.12 2.13 2.12 5.58-2e-3 7.7-2.12 2.12-5.56 2.12-7.69 1e-3 -2.12-2.13-2.12-5.58 0-7.7m0-23.6c2.12-2.12 5.56-2.12 7.69 0 2.12 2.13 2.12 5.57 0 7.7-2.12 2.12-5.56 2.12-7.69-6e-3 -2.12-2.12-2.12-5.57 0-7.7m11.8 11.8c2.13-2.13 5.58-2.13 7.69 0 2.12 2.12 2.12 5.57 0 7.69-2.12 2.13-5.56 2.13-7.69 0-2.12-2.12-2.12-5.57 0-7.69m2e-3 -23.6c2.12-2.13 5.57-2.13 7.69 0 2.12 2.12 2.12 5.56 0 7.69-2.12 2.13-5.56 2.13-7.69 0-2.12-2.12-2.12-5.57 0-7.69m11.8 11.8c2.12-2.12 5.57-2.12 7.69 0 2.12 2.13 2.12 5.57 0 7.7-2.12 2.13-5.57 2.13-7.69 0-2.12-2.12-2.12-5.56 0-7.7m58.8-17.9c0-8.58-6.96-15.6-15.6-15.6h-173c-8.59 0-15.6 6.96-15.6 15.6v59.4h23.2l-23.2 54.3v59.4c0 8.59 6.96 15.6 15.6 15.6h173c8.59 0 15.6-6.96 15.6-15.6v-59.4h-23.2l23.2-54.3z" fill="#2a667f" class="svg-elem-3"></path>'+
			   '</g></svg>';


function crc16(str) {
	const crcTable = [];
	const polynomial = 0xA001;
  
	for (let i = 0; i < 256; i++) {
		let crc = i;
		for (let j = 0; j < 8; j++) {
			crc = (crc & 1) ? ((crc >>> 1) ^ polynomial) : (crc >>> 1);
		}
		crcTable[i] = crc;
	}
	
	let crc = 0xFFFF;

	for (let i = 0; i < str.length; i++) {
		const charCode = str.charCodeAt(i);
		crc = ((crc >>> 8) ^ crcTable[(crc ^ charCode) & 0xFF]) & 0xFFFF;
	}
	
	return crc.toString(16).toUpperCase().padStart(4, '0');
}

function nl2br(str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function fromBytesToString(bytes) {
	const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  
	bytes = Math.max(bytes, 0);
	const pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
	const limitedPow = Math.min(pow, units.length - 1);
  
	bytes /= Math.pow(1024, limitedPow);
  
	return bytes.toFixed(2) + ' ' + units[limitedPow];
  }

function fromStringToBytes(spacestr) {
	let returnNumber = parseFloat(spacestr);

	switch (spacestr.slice(-1)) {
	  case 'T':
		returnNumber *= 1024;
	  case 'G':
		returnNumber *= 1024;
	  case 'M':
		returnNumber *= 1024;
	  case 'K':
		returnNumber *= 1024;
	  default:
		break;
	}

	return returnNumber;
}

function calculateFreePercent(used, free) {
	const usedTmp = typeof used === "string" ? fromStringToBytes(used) : used;
	const freeTmp = typeof free === "string" ? fromStringToBytes(free) : free;

	const result = freeTmp / (freeTmp + usedTmp);
	return result * 100;
}

function implodeWithKeys(glue, array, symbol = ': ') {
    return Object.keys(array)
	  .map((key) => key + symbol + array[key])
	  .join(glue);
}

function daysToNow(timestamp) {
    const currentDate = new Date();
    const diffDate = new Date(timestamp * 1000);

    const timeDifference = currentDate - diffDate; 

    const daysDifference = Math.floor(timeDifference / (1000 * 60 * 60 * 24));

    return daysDifference;
}

//endregion utils


function getLastSnap(zsnapshots) {
	var lastsnap = zsnapshots[0];

	for (snap in zsnapshots) {
		if (snap['creation'] > lastsnap['creation']) {
			lastsnap = snap;
		}
	}

	return lastsnap;
}

function getPoolStatusColor(status) {
	switch(status) {
		case 'ONLINE':
			return 'green';
		case 'DEGRADED':
			return 'yellow';
		case 'FAULTED':
			return 'red';
		case 'OFFLINE':
			return 'blue';
		case 'UNAVAIL':
			return 'grey';
		case 'REMOVED':
			return 'grey';
	}

	return 'grey';
}

function getPoolStatusMsg(status) {
	switch (status) {
		case 'ONLINE':
			return 'The pool is in normal working order';
		case 'DEGRADED':
			return 'One or more devices with problems. Functioning in a degraded state';
		case 'FAULTED':
			return 'One or more devices could not be used. Pool unable to continue functioning';
		case 'OFFLINE':
			return 'One or more devices has been explicitly taken offline by the administrator ';
		case 'UNAVAIL':
			return 'One or more devices or virtual devices cannot be opened';
		case 'REMOVED':
			return 'One or more devices were physically removed while the system was running';
	}

	return 'Status Unknown'
}

function getPoolShowButtonStatus(show_status) {
	return show_status == true ? "Hide Datasets" : "Show Datasets";
}

function getPoolShowStatus(zpool) {
	var cookie = document.cookie;

	return cookie['zdataset-'+zpool] == true ? true : false;
}

function generatePoolTableRows(zpool, devices, show_status) {
	const show_button_status = getPoolShowButtonStatus(show_status);
	const status_color = getPoolStatusColor(zpool['Health']);
	const status_msg = getPoolStatusMsg(zpool['Health']);

	// Name and devices
	var tr = '<td id="zpool-attribute-pool"><a class="info hand"><i id="zpool-'+zpool['Pool']+'" class="fa fa-circle orb '+status_color+'-orb"></i><span>'+nl2br(devices)+'</span></a> '+zpool['Pool']+'</td>';

	// Health
	tr += '<td id="zpool-attribute-health"><a class="info hand"><i class="fa fa-heartbeat" style="color:'+status_color+'"></i><span>'+status_msg+'</span></a> '+zpool['Health']+'</td>';

	// Buttons
	tr += '<td id="zpool-attribute-name"><button type="button" id="show-zpool-'+zpool['Pool']+'" onclick="togglePoolTable(\'show-zpool-'+zpool['Pool']+'\', \'zdataset-'+zpool['Pool']+'\');">'+show_button_status+'</button>'; 
	tr += '<button type="button" onclick="createDataset(\''+zpool['Pool']+'\')";">Create Dataset</button></td>';

	// Size
	tr += '<td id="zpool-attribute-size">'+zpool['Size']+'</td>'; 

	// Mountpoint
	tr += '<td id="zpool-attribute-mountpoint">'+zpool['MountPoint']+'</td>'; 

	// Refer
	tr += '<td id="zpool-attribute-refer">'+zpool['Refer']+'</td>'; 

	// Used
	var percent = 100-Math.round(calculateFreePercent(zpool['Used'], zpool['Free']));
	tr += '<td id="zpool-attribute-used"><div class="usage-disk"><span style="position:absolute; width:'+percent+'%" class=""><span>'+zpool['Used']+'B</span></div></td>';

	// Free
	tr += '<td id="zpool-attribute-free"><div class="usage-disk"><span style="position:absolute; width:'+(100-percent)+'%" class=""><span>'+zpool['Free']+'B</span></div></td>';

	// Snapshots
	tr += '<td id="zpool-attribute-snapshots"><i class="fa fa-camera-retro icon"></i>'+(zpool['Snapshots'] == null ? 0 : zpool['Snapshots'])+'</td>';

	return tr; 
}

function generateDatasetRow(zpool, zdataset, show_status, destructive_mode, snap_max_days_alert) {
	var tr = '<tr class="zdataset-'+zpool+' '+zdataset+'" style="display: '+show_status+'">';
	tr += '<td></td><td></td><td>';

	const creationDate = new Date(zdataset['creation'] * 1000);

	const properties = {
		'Creation Date' : creationDate.toISOString(),
		'Compression' : zdataset['compression'],
		'Compress Ratio' : zdataset['compressratio']/100,
		'Record Size' : fromBytesToString(zdataset['recordsize']),
		'Access Time' : zdataset['atime'],
		'XAttr' : zdataset['xattr'],
		'Primary Cache' : zdataset['primarycache'],
		'Encryption' : zdataset['encryption'],
		'Key Status' : zdataset['keystatus'],
		'Quota' : fromBytesToString(zdataset['quota']),
		'Read Only' : zdataset['readonly'],
		'Case Sensitive' : zdataset['casesensitivity'],
		'Sync' : zdataset['sync'],
		'Origin' : zdataset['origin'] ?? '',
		'Space used by Snaps' : fromBytesToString(zdataset['usedbysnapshots'])
	};

	const icon_color = 'grey';
			
	if (zdataset['snapshots'] > 0) {
		const snap = getLastSnap(zdataset['snapshots']);

		snapdate = new Date(snap['creation']);
				
		if (daysToNow(snap['creation']) > snap_max_days_alert) {
			icon_color = 'orange';
		} else {
			icon_color = '#486dba';
		}
				
		properties['Last Snap Date'] = snapdate.toISOString();
		properties['Last Snap'] = snap['name'];
	}

	const depth = zdataset['name'].split('/').length - 1;

	for (let i = 1; i <= depth; i++) {
    	tr += '&emsp;&emsp;';
	}

	tr += '<a class="info hand"><i class="fa fa-hdd-o icon" style="color:'+icon_color+'" onclick="toggleDataset(\''+zdataset['name']+'\');"></i>';
	tr += '<span>'+implodeWithKeys('<br>', properties)+'</span></a>';


	if (zdataset['child'] > 0) {
		tr += '<i class="fa fa-minus-square fa-append" name="'+zdataset['name']+'"></i>';
	}

	if (zdataset['origin'] !== undefined) {
		tr += '<i class="fa fa-clone fa-append"></i>';
	}

	if (zdataset['keystatus'] != 'none') {
		if (zdataset['keystatus'] == 'available') {
			tr += '<i class="fa fa-unlock fa-append"></i>';
		} else {
			tr += '<i class="fa fa-lock fa-append"></i>';
		}
	}

	tr += zdataset['name'].substring(zdataset['name'].lastIndexOf("/") + 1);
	tr += '</td>';

	// Actions

	tr += '<td>';
	var id = crc16(zdataset['name']);
	var snap_count = 0;

	if (zdataset['snapshots'] !== undefined ) {
		snap_count = zdataset.snapshots.length;
	} else {
	}

	tr += '<button type="button" id="'+id+'" onclick="addDatasetContext(\''+zpool+'\', \''+zdataset['name']+'\', '+snap_count+', \''+id+'\', '+destructive_mode+', \''+zdataset['keystatus']+'\'';
	
	if (zdataset['origin'] !== undefined) {
		tr += ',\''+zdataset['origin']+'\'';
	}

	tr += ');" class="zfs_compact">Actions</button></span>';
	tr += '</td>';

	//mountpoint
	tr += '<td>';
	if (zdataset['mountpoint'] != "none") {
		tr += zdataset['mountpoint'];
	}

	tr += '</td>';

	// Referr
	tr += '<td>';
	tr += fromBytesToString(zdataset['referenced']);
	tr += '</td>';

	// Used
	tr += '<td>';
	var percent = 100-Math.round(calculateFreePercent(zdataset['used'], zdataset['available']));
	tr += '<div class="usage-disk"><span style="width:'+percent+'%" class=""><span>'+fromBytesToString(zdataset['used'])+'</span></div>';
	tr += '</td>';

	// Free
	tr += '<td>';
	tr += '<div class="usage-disk"><span style="width:'+(100-percent)+'%" class=""><span>'+fromBytesToString(zdataset['available'])+'</span></div>';
	tr += '</td>';

	// Snapshots

	tr += '<td>';
	
	tr += '<i class="fa fa-camera-retro icon" style="color:'+icon_color+'"></i> ';
	tr += zdataset['snapshots'] !== undefined ? zdataset['snapshots'] : 0 ;

	if (zdataset['mountpoint'] != "none") {
		tr += ' <a href="/Main/Browse?dir='+zdataset['mountpoint']+'"><i class="icon-u-tab zfs_bar_button" title="Browse '+zdataset['mountpoint']+'"></i></a>';
	}

	tr += '</td>';
	tr += '</tr>';

	return tr;
}

function generateDatasetArrayRows(zpool, datasets, show_status, destructive_mode, snap_max_days_alert) {
	var tr = '<tr class="zdataset-'+zpool+' '+zpool+'" style="display: '+show_status+'">';

	if (Object.keys(datasets.child).length == 0) {
		return tr;
	}

	Object.values(datasets.child).forEach((zdataset) => {
		tr += generateDatasetRow(zpool, zdataset, show_status, destructive_mode, snap_max_days_alert);

		if (Object.keys(zdataset.child).length > 0) {
			tr += generateDatasetArrayRows(zpool, zdataset, show_status, destructive_mode, snap_max_days_alert);
		}
	});

	return tr;
}

function updateFullBodyTable(data, destructive_mode, snap_max_days_alert) {
	var html_pools = "";

	Object.values(data.pools).forEach((zpool) => {
		const show_status = getPoolShowStatus(zpool['Pool']);

		zfs_table_body = document.getElementById('zfs_master_body');

		html_pools += '<tr>';
		html_pools += generatePoolTableRows( zpool, data['devices'][zpool['Pool']], show_status);
		html_pools += generateDatasetArrayRows( zpool['Pool'], data['datasets'][zpool['Pool']], show_status, destructive_mode, snap_max_days_alert);
		html_pools += '</tr>';
	});

	zfs_table_body.innerHTML = html_pools;
}