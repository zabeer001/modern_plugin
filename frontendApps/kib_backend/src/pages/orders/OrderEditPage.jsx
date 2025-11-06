import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { backendUrl } from "../../../env";
import { toast, ToastContainer } from "react-toastify";
import Select from "react-select";

function OrderEditPage() {
  const { uniq_id } = useParams();
  const navigate = useNavigate();

  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [vendors, setVendors] = useState([]);
  const [vendorLoading, setVendorLoading] = useState(false);

  // ✅ Fetch single order by ID
  useEffect(() => {
    const fetchOrder = async () => {
      try {
        const res = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/orders/${uniq_id}`);
        const data = await res.json();
        if (data.status) setOrder(data.data);
        else console.warn("Order not found:", data.message);
      } catch (error) {
        console.error("Error fetching order:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchOrder();
  }, [uniq_id]);

  // ✅ Fetch vendor list with JWT auth
  useEffect(() => {
    const fetchVendors = async () => {
      setVendorLoading(true);
      try {
        const token = localStorage.getItem("jwt_token");
        if (!token) {
          console.warn("No JWT token found in localStorage.");
          return;
        }

        const res = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/users?role=vendor`, {
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`,
          },
        });

        const data = await res.json();

        console.log(data);
        
        if (data.status && Array.isArray(data.data)) {
          const vendorOptions = data.data.map((v) => ({
            value: v.id,
            label: `${v.name} (${v.email}) -- id : (${v.id})`,
          }));
          setVendors(vendorOptions);
        } else {
          console.warn("Failed to fetch vendors:", data.message);
        }
      } catch (error) {
        console.error("Error fetching vendors:", error);
      } finally {
        setVendorLoading(false);
      }
    };

    fetchVendors();
  }, []);

  // ✅ Handle generic field change
  const handleChange = (field, value) => {
    setOrder((prev) => ({ ...prev, [field]: value }));
  };

  // ✅ Handle nested shipping fields
  const handleShippingChange = (field, value) => {
    setOrder((prev) => ({
      ...prev,
      shipping: { ...prev.shipping, [field]: value },
    }));
  };

  // ✅ Save updated order
  const handleSave = async (id) => {
    setSaving(true);
    try {
      const token = localStorage.getItem("jwt_token");
      if (!token) {
        alert("Missing authentication token. Please log in again.");
        return;
      }

      const res = await fetch(`${backendUrl}/wp-json/kibsterlp-admin/v1/orders/${id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(order),
      });

      const result = await res.json();
      if (result.status) {
        toast.success(result.message || "Order updated successfully!", {
          position: "top-right",
          autoClose: 3000,
          hideProgressBar: false,
          closeOnClick: true,
          pauseOnHover: true,
          draggable: true,
        });
      } else {
        alert(result.message || "Failed to update order");
      }
    } catch (error) {
      console.error("Error updating order:", error);
      alert("Something went wrong while saving.");
    } finally {
      setSaving(false);
    }
  };

  // ✅ Handle loading states
  if (loading) return <p className="p-6 text-gray-600">Loading...</p>;
  if (!order) return <p className="p-6 text-red-500">Order not found.</p>;

  return (
    <div className="p-8 bg-gray-50 min-h-screen">
      <ToastContainer />
      <div className="flex justify-between">
        <button
          onClick={() => navigate(-1)}
          className="px-4 py-2 mb-4 bg-black text-white rounded font-bold hover:opacity-90"
        >
          ← Back
        </button>

        <div className="text-right">
          <button
            onClick={() => handleSave(order.id)}
            disabled={saving}
            className={`px-6 py-2 rounded-md font-semibold text-white transition ${
              saving ? "bg-gray-400 cursor-not-allowed" : "bg-red-600 hover:bg-red-700"
            }`}
          >
            {saving ? "Saving..." : "Save"}
          </button>
        </div>
      </div>

      <h1 className="text-2xl font-semibold mb-6">
        Edit Order #{order.order_unique_id}
      </h1>

      {/* Layout Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left Section */}
        <div className="lg:col-span-2 space-y-6">
          {/* Order Category */}
          <div className="bg-white shadow rounded-2xl p-5">
            <h2 className="text-lg font-medium mb-3 text-gray-700">
              Order Category
            </h2>
            <div className="border-t pt-3 text-sm text-gray-600">
              <span>{order.category?.title}</span>
            </div>
            <div className="border-t mt-4 pt-3 text-right text-gray-700 font-medium">
              Budget: € {order.budget}
            </div>
          </div>

          {/* Order Info */}
          <div className="bg-white shadow rounded-2xl p-5">
            <h2 className="text-lg font-medium mb-3 text-gray-700">
              Order Details
            </h2>
            <div className="space-y-4 text-sm text-gray-700">
              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Vendor
                </label>
                <Select
                  isClearable
                  isLoading={vendorLoading}
                  options={vendors}
                  placeholder="Search and select vendor..."
                  value={
                    vendors.find(
                      (v) => Number(v.value) === Number(order.vendor_id)
                    ) || null
                  }
                  onChange={(selected) =>
                    handleChange("vendor_id", selected ? selected.value : "")
                  }
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Budget (€)
                </label>
                <input
                  type="number"
                  value={order.budget || ""}
                  onChange={(e) => handleChange("budget", e.target.value)}
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Status
                </label>
                <select
                  value={order.sharing_status}
                  onChange={(e) =>
                    handleChange("sharing_status", e.target.value)
                  }
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                >
                  <option value="not accepted">Not Accepted</option>
                  <option value="accepted">Accepted</option>
                </select>
                <label className="block text-sm font-medium text-gray-700 mt-3">
                  Payment Status
                </label>
                 <select
                  value={order.payment_status}
                  onChange={(e) =>
                    handleChange("payment_status", e.target.value)
                  }
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                >
                  <option value="pending">pending</option>
                  <option value="paid">Paid</option>
                  <option value="unpaid">Unpaid</option>

                </select>
              </div>
            </div>
          </div>

         
        </div>

        {/* Right Section */}
        <div className="space-y-6">
          {/* Customer Info */}
          <div className="bg-white shadow rounded-2xl p-5">
            <h2 className="text-lg font-medium mb-3 text-gray-700">
              Customer
            </h2>
            <div className="text-sm space-y-3">
              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Name
                </label>
                <input
                  type="text"
                  value={order.shipping?.name || ""}
                  onChange={(e) => handleShippingChange("name", e.target.value)}
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Email
                </label>
                <input
                  type="email"
                  value={order.shipping?.email || ""}
                  onChange={(e) =>
                    handleShippingChange("email", e.target.value)
                  }
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Phone
                </label>
                <input
                  type="text"
                  value={order.shipping?.phone || ""}
                  onChange={(e) =>
                    handleShippingChange("phone", e.target.value)
                  }
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          {/* Shipping Info */}
          <div className="bg-white shadow rounded-2xl p-5">
            <h2 className="text-lg font-medium mb-3 text-gray-700">
              Shipping Address
            </h2>
            <div className="text-sm space-y-3">
              <div>
                <label className="block text-sm font-medium text-gray-700">
                  City
                </label>
                <input
                  type="text"
                  value={order.shipping?.city || ""}
                  onChange={(e) => handleShippingChange("city", e.target.value)}
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Zip Code
                </label>
                <input
                  type="text"
                  value={order.shipping?.zip_code || ""}
                  onChange={(e) =>
                    handleShippingChange("zip_code", e.target.value)
                  }
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">
                  Country
                </label>
                <input
                  type="text"
                  value={order.shipping?.country || ""}
                  onChange={(e) =>
                    handleShippingChange("country", e.target.value)
                  }
                  className="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default OrderEditPage;
