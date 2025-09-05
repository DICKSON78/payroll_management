<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="editEmployeeModal">
    <div class="bg-white rounded-xl w-full max-w-md">
        <div class="p-6 border-b">
            <h3 class="text-xl font-semibold">Edit Employee</h3>
        </div>
        <div class="p-6">
            <form>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit-name">Full Name</label>
                    <input type="text" id="edit-name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="John Doe" value="John Abraham">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit-email">Email Address</label>
                    <input type="email" id="edit-email" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="john@company.com" value="john@company.com">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit-department">Department</label>
                    <select id="edit-department" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option value="IT" selected>Information Technology</option>
                        <option value="HR">Human Resources</option>
                        <option value="Finance">Finance</option>
                        <option value="Marketing">Marketing</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit-position">Position</label>
                    <input type="text" id="edit-position" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Software Developer" value="Software Developer">
                </div>
                <div class="flex justify-end">
                    <button type="button" class="text-gray-600 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 mr-2" onclick="closeModal('editEmployeeModal')">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>