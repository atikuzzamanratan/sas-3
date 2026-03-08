function NewWindow(mypage, myname, w, h, scroll) {
    LeftPosition = (screen.width) ? (screen.width - w) / 2 : 0;
    TopPosition = (screen.height) ? (screen.height - h) / 2 : 0;
    settings =
        'height=' + h + ',width=' + w + ',top=' + TopPosition + ',left=' + LeftPosition + ',scrollbars=' + scroll + ',resizable'
    win = window.open(mypage, myname, settings)
}

function exportTableToExcel(tableID, ReportName) {
    let downloadLink;
    const dataType = 'application/vnd.ms-excel';
    const tableSelect = document.getElementById(tableID);
    const parser = new DOMParser();
    const htmlDoc = parser.parseFromString(tableSelect.outerHTML, 'text/html');
    
    // Remove <td> elements that contain <div>
    const tds = htmlDoc.querySelectorAll('td');
    tds.forEach(td => {
        if (td.querySelector('div')) {
            td.remove();
        }
    });

    const tableHTML = htmlDoc.body.innerHTML.replace(/ style="[^"]*"/g, '').replace(/ /g, '%20');

    console.log(tableHTML);
    
    // Specify file name dynamically
    const filename = `${ReportName}.xls`;

    // Create download link element
    downloadLink = document.createElement('a');
    document.body.appendChild(downloadLink);

    if (navigator.msSaveOrOpenBlob) {
        // For Internet Explorer
        const blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        // For other browsers
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}