import React, { useEffect, useState } from 'react';
import { fetchOrders } from '../../api/orders/fetchOrders';
import { Eye, Edit, Trash } from "lucide-react";
import { Link } from "react-router-dom";
import { backendUrl } from '../../../env';
import { toast } from 'react-toastify';
import { showErrorToast, showSuccessToast } from '../../utils/toast';



function OrderIndexPage() {
  const [orders, setOrders] = useState([]);
  const [dataLoading, setDataLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [sharing_status, setSharing_status] = useState('all');
  const [payment_status, setPayment_status] = useState('all');
  const [category_id, setCategory_id] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [categories, setCategories] = useState([]);
  const [categoryLoading, setCategoryLoading] = useState(false);



  const apiBase = `${backendUrl}/wp-json/kibsterlp-admin/v1`;

  useEffect(() => {
    const delay = setTimeout(() => {
      const loadOrders = async () => {
        try {
          setDataLoading(true);
          const token = localStorage.getItem('jwt_token');



          const data = await fetchOrders({
            page: currentPage,
            search: searchTerm.trim() === '' ? undefined : searchTerm.trim(),
            token,
            category_id,
            sharing_status,
            payment_status
          });





          const { orders, totalPages } = data;


          // ✅ Safely handle total_pages (avoid NaN or 0)
          const safeTotalPages = Number(totalPages);
          console.log(safeTotalPages);

          setTotalPages(safeTotalPages > 0 ? safeTotalPages : 1);

          // ✅ Always set orders (fallback empty array)
          setOrders(Array.isArray(orders) ? orders : []);
        } catch (error) {
          console.error('Error fetching orders:', error);
          setOrders([]);
          setTotalPages(1);
        } finally {
          setDataLoading(false);
        }
      };

      loadOrders();
    }, 400);

    return () => clearTimeout(delay);
  }, [currentPage, searchTerm, category_id, sharing_status,payment_status]);


  // ✅ Fetch All Categories
  useEffect(() => {
    const fetchCategories = async () => {
      setCategoryLoading(true);
      try {
        const res = await fetch(`${apiBase}/categories`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const data = await res.json();
        console.log("✅ Categories fetched:", data);




        if (data?.data && Array.isArray(data.data)) {
          setCategories(data.data);

        } else {
          console.warn("⚠️ Invalid category data format:", data);
          setCategories([]);
        }
      } catch (error) {
        console.error("❌ Error fetching categories:", error);
        setCategories([]);
      } finally {
        setCategoryLoading(false);
      }
    };

    fetchCategories();
  }, []);

  const formatCurrency = (amount) =>
    new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount || 0);

  const formatDate = (date) =>
    date
      ? new Date(date).toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
      : 'N/A';

  const handleDelete = async (id) => {
    if (!window.confirm("Are you sure you want to delete this order?")) return;

    const token = localStorage.getItem("jwt_token");

    try {
      const res = await fetch(`${apiBase}/orders/${id}`, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`,
        },
      });

      if (!res.ok) throw new Error("Failed to delete order");

      setOrders((prev) => prev.filter((order) => order.id !== id));

      showSuccessToast("Order deleted successfully!");
    } catch (error) {
      console.error(error);
      showErrorToast("Error deleting order");
    }
  };

  return (
    <div className="!max-w-7xl !mx-auto !mt-4 !p-4">

      {/* Toolbar */}
      <div className="!bg-white !rounded-xl !shadow-sm !border !border-gray-200 !p-4 !mb-6 !flex !flex-wrap !items-center !justify-between">
        <div className="!flex !items-center !space-x-3">
          {/* Search */}
          <div className="!relative !w-52">
            <input
              type="text"
              placeholder="Search..."
              value={searchTerm}
              onChange={(e) => {
                setSearchTerm(e.target.value);
                setCurrentPage(1);
              }}
              className="!block !w-full !pl-8 !pr-3 !py-1.5 !border !border-gray-300 !rounded-lg !text-sm focus:!ring-2 focus:!ring-blue-500 focus:!border-blue-500"
            />
            <svg
              className="!absolute !left-2 !top-1/4 !transform -!translate-y-1/2 !h-4 !w-4 !text-gray-400"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              viewBox="0 0 24 24"
            >
              <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          {/* Category Filter */}
          <select
            value={category_id}
            onChange={(e) => {
              setCategory_id(e.target.value);
              setCurrentPage(1);
            }}
            className="!border !border-gray-300 !rounded-lg !px-[25px] !py-1.5 !text-sm focus:!ring-2 focus:!ring-blue-500 focus:!border-blue-500"
          >
            <option value="all">All Categories</option>
            {categoryLoading ? (
              <option disabled>Loading...</option>
            ) : categories.length > 0 ? (
              categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.title}
                </option>
              ))
            ) : (
              <option disabled>No categories found</option>
            )}
          </select>
        </div>

        {/* Status Filter */}
        <div className="!flex !items-center !space-x-3">
          <select
            value={sharing_status}
            onChange={(e) => {
              setSharing_status(e.target.value);
              setCurrentPage(1);
            }}
            className="!border !border-gray-300 !rounded-lg !px-[25px] !py-1.5 !text-sm focus:!ring-2 focus:!ring-blue-500 focus:!border-blue-500"
          >
            <option value="all">Status</option>
            <option value="not accepted">not accepted</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
          </select>
          <select
            value={payment_status}
            onChange={(e) => {
              setPayment_status(e.target.value);
              setCurrentPage(1);
            }}
            className="!border !border-gray-300 !rounded-lg !px-[25px] !py-1.5 !text-sm focus:!ring-2 focus:!ring-blue-500 focus:!border-blue-500"
          >
            <option value="all">Payment Status</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
             <option value="cancel">Cancel</option>
          </select>
        </div>
        
      </div>

      {/* Orders Table */}
      <div className="!bg-white !rounded-xl !shadow-sm !border !border-gray-200 !overflow-hidden">
        {dataLoading ? (
          <div className="!flex !justify-center !items-center !p-10">
            <div className="!animate-spin !rounded-full !h-8 !w-8 !border-b-2 !border-blue-600"></div>
            <p className="!ml-3 !text-gray-500">Loading orders...</p>
          </div>
        ) : orders.length === 0 ? (
          <div className="!p-8 !text-center !text-gray-500">No orders found.</div>
        ) : (
          <table className="!min-w-full !divide-y !divide-gray-200">
            <thead className="!bg-gray-50">
              <tr>
                {['Order', 'Vendor', 'Email', 'Phone', 'Payment Status', 'Budget & Price', 'Location', 'Actions'].map((h) => (
                  <th key={h} className="!px-6 !py-3 !text-left !text-xs !font-semibold !text-gray-500 !uppercase">
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="!bg-white !divide-y !divide-gray-100">
              {orders.map((order, index) => (
                <tr key={order.id || index} className="!hover:bg-gray-50 !cursor-pointer">
                  <td className="!px-6 !py-3 !text-sm !text-gray-800">
                    <div className="!flex !items-center">
                      <div className="!h-8 !w-8 !rounded-lg !bg-blue-100 !flex !items-center !justify-center !font-semibold !text-blue-600 !mr-3">
                        #{order.id}
                      </div>
                      <div>
                        <div className="!font-medium">{order.order_title}</div>
                        <div className="!text-xs !text-gray-500">{order.order_unique_id}</div>
                      </div>
                    </div>
                  </td>
                  <td className="!px-6 !py-3 !text-sm !text-gray-700">
                    {order.vendor_id !== null &&
                      order.vendor_id !== undefined &&
                      order.vendor_id !== "" &&
                      Number(order.vendor_id) !== 0 ? (
                      order.vendor_id
                    ) : (
                      <button className="!bg-red-500 !text-white !text-xs !px-2 !py-1 !rounded">
                        Not Accepted
                      </button>
                    )}
                  </td>
                  <td className="!px-6 !py-3 !text-sm !text-gray-700">{order.email}</td>
                  <td className="!px-6 !py-3 !text-sm !text-gray-700">{order.phone}</td>
                  <td className="!px-6 !py-3 !text-sm">
                    <span
                      className={`!inline-flex !items-center !px-3 !py-1 !rounded-full !text-xs !font-medium ${order.payment_status === 'pending'
                        ? '!bg-yellow-100 !text-yellow-800'
                        : order.payment_status === 'paid'
                          ? '!bg-green-100 !text-green-800'
                          : '!bg-gray-100 !text-gray-800'
                        }`}
                    >
                      {order.payment_status}
                    </span>
                  </td>
                  <td className="!px-6 !py-3 !text-sm">
                    <div className="!text-gray-500">Budget: {formatCurrency(order.budget)}</div>
                    <div className="!text-gray-900 !font-semibold">
                      Price: {formatCurrency(order.price)}
                    </div>
                  </td>
                  <td className="!px-6 !py-3 !text-sm !text-gray-600">{order.zip_code || 'N/A'}</td>
                  <td className="!px-6 !py-3 flex items-center gap-3 !mt-[30px]">
                    {/* View */}
                    <Link
                      to={`/admin/orders/${order.order_unique_id}`}
                      className="text-blue-600 hover:text-blue-800"
                    >
                      <Eye className="w-4 h-4" />
                    </Link>

                    {/* Edit (as link under /admin) */}
                    <Link
                      to={`/admin/orders/edit/${order.order_unique_id}`}
                      className="text-green-600 hover:text-green-800"
                    >
                      <Edit className="w-4 h-4" />
                    </Link>

                    {/* Delete */}
                    <button
                      className="!text-red-600 hover:text-red-800"
                      onClick={() => handleDelete(order.id)}
                    >
                      <Trash className="w-4 h-4" />
                    </button>
                    {/* <button
                      className="text-red-600 hover:text-red-800"
                      onClick={() => handleDelete(order.id)} // optional
                    >
                      <Trash className="w-4 h-4" />
                    </button> */}
                  </td>

                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Pagination */}
      <div className="!flex !justify-center !items-center !mt-6 !space-x-2">
        <button
          disabled={currentPage <= 1}
          onClick={() => setCurrentPage((p) => Math.max(p - 1, 1))}
          className="!px-3 !py-1 !bg-gray-100 !rounded-lg !text-sm !text-gray-700 disabled:!opacity-50"
        >
          Previous
        </button>

        <span className="!text-sm !text-gray-600">
          Page {currentPage} of {totalPages}
        </span>

        <button
          disabled={currentPage >= totalPages}
          onClick={() => setCurrentPage((p) => Math.min(p + 1, totalPages))}
          className="!px-3 !py-1 !bg-gray-100 !rounded-lg !text-sm !text-gray-700 disabled:!opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  );
}

export default OrderIndexPage;
