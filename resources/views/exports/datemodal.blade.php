<!-- resources/views/modals/date_modal.blade.php -->
<div class="modal fade" id="dateModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateModalLabel">Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dateForm" method="post" action="">
                @csrf
                <div class="modal-body">        
                        <div class="form-group">
                            <label for="startData required">Dari</label>
                            <input type="date" class="form-control" id="startData" name="startData" required>
                        </div>
                        <div class="form-group">
                            <label for="endDate required">Hingga</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" required>
                        </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" id="applyDates">Eksport</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).ready(function(){

        $action = $("#actionName").val();

        $('#dateForm').attr('action', $action);

        $("#excelBtn").click(function(){
            $("#dateModal").modal('show');
        });

        $('#applyDates').on('click', function(event) {
            var startDate = new Date($('#startData').val());
            var endDate = new Date($('#endDate').val());

            if (startDate > endDate) {
                alert('End date must be greater than start date.');
                event.preventDefault();
            }
        });

    });
</script>
