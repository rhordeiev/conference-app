export default function downloadCsvFile(csvContent, fileName) {
    const csvFileLink = document.createElement('a');
    const blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8;'});
    csvFileLink.href = URL.createObjectURL(blob);
    csvFileLink.setAttribute('download', fileName);
    csvFileLink.click();
}
