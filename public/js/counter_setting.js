$(':input[type="number"]').on('keyup', function() {
    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
});

$(':input[type="number"]').on('wheel', function() {
    $(this).blur();
});
