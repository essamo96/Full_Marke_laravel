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
                 var isSuccess = data.status ? (data.status === 'success') : (data.success !== false);

                 $('#confirm').modal('hide');

                 if (isSuccess) {
                     Swal.fire({
                         text: data.message || "تم الحذف بنجاح",
                         title: "نجاح",
                         icon: "success",
                         buttonsStyling: false,
                         showConfirmButton: false,
                         timer: 3000
                     });
                     table.draw();
                 } else {
                     Swal.fire({
                         text: data.message || "تعذر تنفيذ عملية الحذف.",
                         title: "خطأ",
                         icon: "error"
                     });
                 }
             },
             error: function (xhr) {
                 $('#confirm').modal('hide');
                 var message = (xhr.responseJSON && xhr.responseJSON.message) || "تعذر تنفيذ عملية الحذف. قد تكون هذه العناصر مرتبطة ببيانات أخرى.";
                 Swal.fire({
                     text: message,
                     title: "خطأ",
                     icon: "error"
                 });
             }
         });
     });
     $('#delete_id').val(href);
 });

