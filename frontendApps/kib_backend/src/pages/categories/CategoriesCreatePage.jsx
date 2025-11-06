import React, { useState } from "react";
import { useNavigate } from 'react-router-dom';
import { backendUrl } from "../../../env";


function CategoriesCreatePage() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        title: "",
        description: "",
    });

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        console.log("Submitting category:", formData);

        // Replace this with your actual backend URL


        // Your Bearer token (you can store this securely)
        const token = localStorage.getItem('jwt_token');

        try {
            const response = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/categories`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${token}`, // 👈 Authorization header
                },
                body: JSON.stringify(formData), // Send your category data
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error("Error creating category:", errorData);
                alert(`Error: ${errorData.message || "Failed to create category"}`);
                return;
            }

            const result = await response.json();
            console.log("Category created successfully:", result);
            alert("✅ Category created successfully!");
        } catch (error) {
            console.error("Request failed:", error);
            alert("❌ Something went wrong while creating the category.");
        }
    };

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
                    Create New Category
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

                    {/* Submit Button */}
                    <div className="flex justify-end">
                        <button
                            type="submit"
                            className="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition-all"
                        >
                            Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default CategoriesCreatePage;
