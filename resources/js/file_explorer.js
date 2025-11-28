document.querySelectorAll("tr[data-folder-hash]").forEach((row) => {
    row.addEventListener("click", () => {
        const folderHash = row.getAttribute("data-folder-hash");
        window.location.href = `/admin/files/folder/${folderHash}`;
    });
});
