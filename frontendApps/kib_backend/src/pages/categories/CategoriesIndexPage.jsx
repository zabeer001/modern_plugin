import React, { useEffect, useState } from 'react';
import { backendUrl } from '../../../env';
import { Link } from 'react-router-dom';
import { Edit, Trash } from 'lucide-react';
import { showErrorToast, showSuccessToast } from '../../utils/toast'; // ✅ Correct imports

function CategoriesIndexPage() {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [sortBy, setSortBy] = useState('latest'); // latest | oldest | title

  useEffect(() => {
    fetchCategories();
  }, []);

  // ✅ Fetch categories
  const fetchCategories = async () => {
    try {
      setLoading(true);
      const response = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/categories`);
      if (!response.ok) throw new Error(`HTTP Error ${response.status}`);
      const data = await response.json();
      setCategories(data.data || []);
    } catch (error) {
      console.error('Error fetching categories:', error);
      showErrorToast('Failed to fetch categories.');
    } finally {
      setLoading(false);
    }
  };

  // ✅ Delete handler
  const handleDelete = async (id) => {
    const confirmed = window.confirm('Are you sure you want to delete this category?');
    if (!confirmed) return;

    const token = localStorage.getItem('jwt_token');

    try {
      const response = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/categories/${id}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });

      const result = await response.json();

      if (response.ok) {
        showSuccessToast('Category deleted successfully!');
        setCategories((prev) => prev.filter((cat) => cat.id !== id));
      } else {
        showErrorToast(result.message || 'Failed to delete category.');
      }
    } catch (error) {
      console.error('Error deleting category:', error);
      showErrorToast('Error deleting category.');
    }
  };

  // ✅ Filter & sort categories
  const filteredCategories = categories
    .filter(
      (cat) =>
        cat.title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        cat.id?.toString().includes(searchTerm)
    )
    .sort((a, b) => {
      if (sortBy === 'title') return a.title.localeCompare(b.title);
      if (sortBy === 'oldest') return new Date(a.created_at) - new Date(b.created_at);
      return new Date(b.created_at) - new Date(a.created_at);
    });

  // ✅ Loading UI
  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600 text-lg font-medium">Loading categories...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto mt-4 p-4">
      {/* Toolbar */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center justify-between">
        <div className="flex items-center space-x-3">
          {/* Search */}
         

         
        </div>

        <Link
          to="/admin/categories/create"
          className="px-4 py-2 bg-black !text-white rounded font-bold hover:opacity-90"
        >
          Create Category
        </Link>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
              <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Title</th>
              <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
              <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-100">
            {filteredCategories.length === 0 ? (
              <tr>
                <td colSpan="4" className="text-center py-6 text-gray-500">
                  No categories found.
                </td>
              </tr>
            ) : (
              filteredCategories.map((cat) => (
                <tr key={cat.id} className="hover:bg-gray-50 transition-colors duration-100">
                  <td className="px-6 py-3 text-sm font-medium text-gray-700">#{cat.id}</td>
                  <td className="px-6 py-3 text-sm font-semibold text-gray-800">{cat.title}</td>
                  <td className="px-6 py-3 text-sm text-gray-600">{cat.description}</td>
                  <td className="px-6 py-3 text-sm text-gray-500">
                    <div className="flex items-center gap-3">
                      {/* Edit */}
                      <Link
                        to={`/admin/categories/edit/${cat.id}`}
                        className="text-green-600 hover:text-green-800"
                      >
                        <Edit className="w-4 h-4" />
                      </Link>

                      {/* Delete */}
                      <button
                        className="text-red-600 hover:text-red-800"
                        onClick={() => handleDelete(cat.id)}
                      >
                        <Trash className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default CategoriesIndexPage;
