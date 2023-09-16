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