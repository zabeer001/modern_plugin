import React, { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { backendUrl } from "../../../env";

function CategoriesEditPage() {
  const navigate = useNavigate();
  const { id } = useParams();

  const [loading, setLoading] = useState(true);
  const [formData, setFormData] = useState({
    title: "",
    description: "",
  });

  // ✅ Fetch category by ID on mount
  useEffect(() => {
    const fetchCategory = async () => {
      try {
        const token = localStorage.getItem("jwt_token");
        const res = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/categories/${id}`, {
          headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`,
          },
        });

        if (!res.ok) {
          const err = await res.json();
          console.error("Failed to fetch category:", err);
          alert(`Error: ${err.message || "Failed to load category"}`);
          setLoading(false);
          return;
        }

        const data = await res.json();
        console.log("Category:", data);

        // Adjust if backend wraps data inside `data`
        const cat = data?.data || data;
        setFormData({
          title: cat.title || "",
          description: cat.description || "",
        });
      } catch (err) {
        console.error("Error fetching category:", err);
        alert("❌ Failed to fetch category details.");
      } finally {
        setLoading(false);
      }
    };

    fetchCategory();
  }, [id]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  // ✅ Handle PUT (update)
  const handleSubmit = async (e) => {
    e.preventDefault();
    console.log("Updating category:", formData);

    const token = localStorage.getItem("jwt_token");
    try {
      const res = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/categories/${id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`,
        },
        body: JSON.stringify(formData),
      });

      if (!res.ok) {
        const err = await res.json();
        console.error("Error updating category:", err);
        alert(`Error: ${err.message || "Failed to update category"}`);
        return;
      }

      const result = await res.json();
      console.log("Category updated successfully:", result);
      alert("✅ Category updated successfully!");
      navigate(-1);
    } catch (error) {
      console.error("Update failed:", error);
      alert("❌ Something went wrong while updating the category.");
    }
  };

  if (loading) return <p className="p-6 text-gray-500">Loading...</p>;

  return (
    <div>
      <button
        onClick={() => navigate(-1)}
        className="px-4 py-2 mb-4 bg-black text-white rounded font-bold hover:opacity-90"
      >
        ← Back
      </button>

      <div className="max-w-2xl mx-auto bg-white shadow-sm border border-gray-200 rounded-xl p-6 mt-8">
        <h1 className="text-2xl font-semibold text-gray-800 mb-6">
          Edit Category #{id}
        </h1>

        <form onSubmit={handleSubmit} className="space-y-5">
          {/* Title */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Title
            </label>
            <input
              type="text"
              name="title"
              placeholder="Enter category title"
              value={formData.title}
              onChange={handleChange}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>

          {/* Description */}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Description
            </label>
            <textarea
              name="description"
              placeholder="Enter category description"
              value={formData.description}
              onChange={handleChange}
              rows={4}
              className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
            />
          </div>

          {/* Submit */}
          <div className="flex justify-end">
            <button
              type="submit"
              className="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition-all"
            >
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default CategoriesEditPage;
