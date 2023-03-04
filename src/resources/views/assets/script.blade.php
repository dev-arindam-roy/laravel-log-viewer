<script type="text/javascript">
function checkallCheckbox() {
    if (document.querySelector('#checkall:checked') !== null ) {
        checkAll();
    } else {
        uncheckAll();
    }
}
function checkAll() {
    var ele = document.getElementsByClassName('ischeckme');
    if (ele.length) {
        for (var i=0; i < ele.length; i++) {
            if (ele[i].type == 'checkbox') {
                ele[i].checked = true;  
            }  
        }  
    }
}
function uncheckAll() {
    var ele = document.getElementsByClassName('ischeckme');
    if (ele.length) {
        for (var i=0; i < ele.length; i++) {
            if (ele[i].type == 'checkbox') {
                ele[i].checked = false;  
            }  
        }  
    }
}
function closeCurrentWindow() {
    window.close();
}
</script>