const entries = document.getElementsByClassName('log-module__entry')
for (let index = 0; index < entries.length; index++) {
    const entry = entries[index];
    console.log(entry)
    const dataId = entry.id.replace('entry-', '')
    entry.addEventListener('click', () => {
        console.log('click')
        const data = document.getElementById(`entry-data-${dataId}`)
        const cssClass = 'log-module__data--expanded'
        data.classList.contains(cssClass) ? data.classList.remove(cssClass) : data.classList.add(cssClass)
    })
}
