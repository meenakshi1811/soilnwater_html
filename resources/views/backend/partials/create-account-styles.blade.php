<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
#createUserModal .modal-dialog {
    max-height: calc(100vh - 2rem);
    margin: 1rem auto;
}
#createUserModal .modal-content {
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
#createUserModal .create-user-form {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
}
#createUserModal .modal-body {
    overflow-y: auto;
    flex: 1 1 auto;
}
#createUserModal .modal-footer {
    flex-shrink: 0;
    border-top: 1px solid rgba(148, 163, 184, .22);
    background: #fff;
}
.pac-container { z-index: 2000 !important; }
</style>
