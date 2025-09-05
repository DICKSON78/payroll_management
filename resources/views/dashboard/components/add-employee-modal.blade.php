<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="employeeModal">
    <div class="bg-white rounded-xl w-full max-w-md">
        <div class="p-6 border-b">
            <h3 class="text-xl font-semibold">Add New Employee</h3>
        </div>
        <div class="p-6">
            <form>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="name">Full Name</label>
                    <input type="text" id="name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="John Doe">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="email">Email Address</label>
                    <input type="email" id="email" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="john@company.com">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="department">Department</label>
                    <select id="department" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option selected>Select Department</option>
                        <option value="IT">Information Technology</option>
                        <option value="HR">Human Resources</option>
                        <option value="Finance">Finance</option>
                        <option value="Marketing">Marketing</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="position">Position</label>
                    <input type="text" id="position" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Software Developer">
                </div>
                <div class="flex justify-end">
                    <button type="button" class="text-gray-600 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 mr-2" onclick="closeModal('employeeModal')">Cancel</button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>