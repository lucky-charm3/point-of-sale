<!-- add a data -->
<div id="addModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="closeModal('addModal')">&times;</span>
    <h2 id="addModalTitle">Add New</h2>
    <form id="addForm">
      <div id="addModalBody">
      </div>
      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
</div>

<div id="editModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="closeModal('editModal')">&times;</span>
    <h2 id="editModalTitle">Edit</h2>
    <form id="editForm">
      <input type="hidden" name="id" id="editId">
      <div id="editModalBody">
      </div>
      <button type="submit" class="btn btn-warning">Update</button>
    </form>
  </div>
</div>


<div id="viewModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="closeModal('viewModal')">&times;</span>
    <h2 id="viewModalTitle">Details</h2>
    <div id="viewModalBody">
         </div>
    <div class="modal-actions">
      <button class="btn btn-primary" onclick="closeModal('viewModal')">Close</button>
    </div>
  </div>
</div>

<div id="deleteModal" class="modal hidden">
  <div class="modal-content">
    <div class="modal-message"></div>
    <div class="modal-actions">
      <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
      <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
  </div>
  </div>
</div>


<div id="toast" class="toast hidden">
  <p id="toastMessage"></p>
</div>
