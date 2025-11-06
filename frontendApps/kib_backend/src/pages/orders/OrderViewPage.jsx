import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { useNavigate } from "react-router-dom";

function OrderViewPage() {
    const { uniq_id } = useParams();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchOrder = async () => {
            try {
                const res = await fetch(
                    `/wp-json/kibsterlp-admin/v1/orders/${uniq_id}`
                );
                const data = await res.json();
                if (data.status) setOrder(data.data);
            } catch (error) {
                console.error("Error fetching order:", error);
            } finally {
                setLoading(false);
            }
        };
        fetchOrder();
    }, [uniq_id]);

    if (loading) return <p className="p-6 text-gray-600">Loading...</p>;
    if (!order) return <p className="p-6 text-red-500">Order not found.</p>;

    return (
        <div className="p-6 bg-gray-50 min-h-screen">

            <button
                onClick={() => navigate(-1)} // 👈 Go back to previous page
                className="px-4 py-2 mb-4 !bg-black !text-white rounded font-bold"
            >
                ← Back
            </button>
            <h1 className="text-2xl font-semibold mb-6">
                Order #{order.order_unique_id}
            </h1>

            {/* Order Summary */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-5">
                {/* Left Section */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Items Card */}
                    <div className="bg-white shadow rounded-2xl p-5">
                        <h2 className="text-lg font-medium mb-3 text-gray-700">
                            Order Category
                        </h2>
                        <div className="border-t pt-3">
                            <div className="flex justify-between text-sm text-gray-600 mb-2">
                                <span>{order.category.title}</span>
                            </div>
                            
                        </div>
                        <div className="border-t mt-4 pt-3 text-right text-gray-700 font-medium">
                           
                             budget: € {order.budget}
                        </div>
                    </div>

                    {/* Timeline */}
                    <div className="bg-white shadow rounded-2xl p-5">
                        <h2 className="text-lg font-medium mb-3 text-gray-700">
                            Order Activity
                        </h2>
                        <ul className="space-y-3 text-sm text-gray-600">
                            <li>
                                <div className="flex justify-between">
                                    <span>Successfully created order in system.</span>
                                    <span className="text-gray-400 text-xs">
                                        {order.created_at}
                                    </span>
                                </div>
                            </li>
                            <li>
                                <div className="flex justify-between">
                                    <span
                                        className={`px-3 py-1 rounded-full text-xs font-medium 
    ${order.sharing_status === "pending"
                                                ? "bg-yellow-100 text-yellow-700"
                                                : order.sharing_status === "approved"
                                                    ? "bg-green-100 text-green-700"
                                                    : order.sharing_status === "rejected"
                                                        ? "bg-red-100 text-red-700"
                                                        : "bg-gray-100 text-gray-700"
                                            }`}
                                    >
                                        {order.sharing_status?.charAt(0).toUpperCase() + order.sharing_status?.slice(1)}
                                    </span>
                                    <span className="text-gray-400 text-xs">
                                        {order.updated_at}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                {/* Right Sidebar */}
                <div className="space-y-6">
                    {/* Order Details */}
                    <div className="bg-white shadow rounded-2xl p-5">
                        <h2 className="text-lg font-medium mb-3 text-gray-700">
                            Order Details
                        </h2>
                        <div className="text-sm text-gray-600 space-y-1">
                            <p><strong>Marketplace:</strong> Custom Store</p>
                            <p><strong>Status:</strong> {order.sharing_status}</p>
                            <p><strong>Created:</strong> {order.created_at}</p>
                            <p><strong>Updated:</strong> {order.updated_at}</p>
                        </div>
                    </div>

                    {/* Customer */}
                    <div className="bg-white shadow rounded-2xl p-5">
                        <h2 className="text-lg font-medium mb-3 text-gray-700">Customer</h2>
                        <div className="text-sm text-gray-600 space-y-1">
                            <p><strong>Name:</strong> {order.shipping?.name}</p>
                            <p><strong>Email:</strong> {order.shipping?.email}</p>
                            <p><strong>Phone:</strong> {order.shipping?.phone}</p>
                        </div>
                    </div>

                    {/* Shipping */}
                    <div className="bg-white shadow rounded-2xl p-5">
                        <h2 className="text-lg font-medium mb-3 text-gray-700">
                            Shipping Address
                        </h2>
                        <div className="text-sm text-gray-600 space-y-1">
                            <p>{order.shipping?.name}</p>
                            <p>{order.shipping?.city || "—"}</p>
                            <p>{order.shipping?.zip_code}</p>
                            <p>{order.shipping?.country || "—"}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default OrderViewPage;
