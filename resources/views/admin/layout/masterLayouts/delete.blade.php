$('#confirm').on('show.bs.modal', function (e) {
     var link = $(e.relatedTarget);
     var href = link.data('href');
     var name = link.data('name');
     var year = link.data('year') || '2024';
     var close_month = link.data('close_month') || '1';
     
     if (!name) {
         var tr = link.closest('tr');
         if(tr.length > 0) {
             name = tr.find('td').eq(1).text().trim();
         }
     }

     if (name && name !== '') {
         $('#confirm .delete-target-name').text('(' + name + ')');
     } else {
         $('#confirm .delete-target-name').text('');
     }

     $('.delete').off('click').on('click', function () {
         $.ajax({
             url: '<?= route($active_menu . '.delete') ?>',
             type: 'POST',
             data: {
                 id: href,
                 year: year,
                 close_month: close_month,
                 _token: '{{ csrf_token() }}'
             },
             success: function (data) {
                 $('#confirm').modal('hide');
                 Swal.fire({
                     text: "تم الحذف بنجاح",
                     title: "نجاح",
                     icon: "success",
                     buttonsStyling: false,
                     showConfirmButton: false,
                     timer: 3000
                 });
                 // toastr[data.status](data.message);
                 table.draw();
             }
         });
     });
     $('#delete_id').val(href);
 });

