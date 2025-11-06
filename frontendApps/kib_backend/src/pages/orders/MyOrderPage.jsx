import React, { useEffect, useState } from 'react';
import { CheckCircle } from "lucide-react";
import { backendUrl } from '../../../env';

function MyOrderPage() {
  const [orders, setOrders] = useState([]);
  const [dataLoading, setDataLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState(''); // default empty => backend defaults to 'unaccepted'
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [processingId, setProcessingId] = useState(null);

  const token = localStorage.getItem('jwt_token');
  const apiBase = `${backendUrl}/wp-json/kibsterlp-admin/v1`;

  // ✅ Fetch orders
  useEffect(() => {
    const delay = setTimeout(() => {
      const loadOrders = async () => {
        try {
          setDataLoading(true);
          if (!token) throw new Error("Missing JWT token");

          const params = new URLSearchParams({
            page: currentPage,
            ...(statusFilter && { status: statusFilter }),
          });

          const res = await fetch(`${apiBase}/my-orders?${params.toString()}`, {
            method: "GET",
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
            },
          });

          if (!res.ok) throw new Error(`API error ${res.status}`);

          const data = await res.json();
          const { orders = [], total_pages = 1 } = data;

          setOrders(Array.isArray(orders) ? orders : []);
          setTotalPages(total_pages > 0 ? total_pages : 1);
        } catch (err) {
          console.error("Error loading orders:", err);
          setOrders([]);
          setTotalPages(1);
        } finally {
          setDataLoading(false);
        }
      };
      loadOrders();
    }, 400);

    return () => clearTimeout(delay);
  }, [currentPage, statusFilter]);

  // ✅ Accept order handler
  const handleAccept = async (orderUniqueId) => {
    const confirmAction = window.confirm("Are you sure you want to accept this order?");
    if (!confirmAction) return;

    try {
      setProcessingId(orderUniqueId);

      const res = await fetch(`${apiBase}/accept-order/${orderUniqueId}`, {
        method: "PUT",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ sharing_status: "Accepted" }),
      });

      const data = await res.json();

      if (data?.status) {
        alert("✅ Order accepted successfully!");
        setOrders((prev) =>
          prev.map((o) =>
            o?.order_unique_id === orderUniqueId
              ? { ...o, sharing_status: "accepted" }
              : o
          )
        );
      } else {
        alert(data?.message || "Failed to accept order.");
      }
    } catch (err) {
      console.error("Error accepting order:", err);
      alert("Error accepting order.");
    } finally {
      setProcessingId(null);
    }
  };

  const formatCurrency = (amount) =>
    new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount || 0);

  return (
    <div className="!max-w-7xl !mx-auto !mt-4 !p-4">
      {/* Toolbar */}
      <div className="!bg-white !rounded-xl !shadow-sm !border !border-gray-200 !p-4 !mb-6 !flex !flex-wrap !items-center !justify-between">
        <h2 className="!text-lg !font-semibold">My Orders</h2>

        {/* Status Filter */}
        <div className="!flex !items-center !space-x-3">
          <select
            value={statusFilter}
            onChange={(e) => {
              setStatusFilter(e.target.value);
              setCurrentPage(1);
            }}
            className="!border !border-gray-300 !rounded-lg !px-[25px] !py-1.5 !text-sm focus:!ring-2 focus:!ring-blue-500 focus:!border-blue-500"
          >
            <option value="">Unaccepted (default)</option>
            <option value="accepted">Accepted</option>
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
                {[
                  'id',
                  'Status',
                  'Budget',
                  'Zip Code',
                  'Name',
                  'Email',
                  'Phone',
                  'Actions',
                ].map((h) => (
                  <th
                    key={h}
                    className="!px-6 !py-3 !text-left !text-xs !font-semibold !text-gray-500 !uppercase"
                  >
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="!bg-white !divide-y !divide-gray-100">
              {orders.map((order) => (
                <tr key={order?.order_unique_id}>
                  <td className="!px-6 !py-3 !text-sm">
                    {order?.sharing_status === 'accepted' ? order?.order_unique_id : '—'}
                  </td>

                  {/* STATUS */}
                  <td className="!px-6 !py-3 !text-sm">
                    <span
                      className={`!inline-flex !items-center !px-3 !py-1 !rounded-full !text-xs !font-medium ${order?.sharing_status === 'accepted'
                        ? '!bg-green-100 !text-green-800'
                        : '!bg-yellow-100 !text-yellow-800'
                        }`}
                    >
                      {order?.sharing_status || 'unaccepted'}
                    </span>
                  </td>

                  {/* BUDGET */}
                  <td className="!px-6 !py-3 !text-sm">
                    {formatCurrency(order?.budget)}
                  </td>

                  {/* ZIP CODE */}
                  <td className="!px-6 !py-3 !text-sm">{order?.zip_code || 'N/A'}</td>

                  {/* NAME */}
                  <td className="!px-6 !py-3 !text-sm">{order?.name || '—'}</td>

                  {/* EMAIL */}
                  <td className="!px-6 !py-3 !text-sm">{order?.email || '—'}</td>

                  {/* PHONE */}
                  <td className="!px-6 !py-3 !text-sm">{order?.phone || '—'}</td>

             

               

                  {/* ACTIONS */}
                  <td className="!px-6 !py-3 !text-sm">
                    {order?.sharing_status !== 'accepted' ? (
                      <button
                        onClick={() => handleAccept(order?.order_unique_id)}
                        disabled={processingId === order?.order_unique_id}
                        className="!bg-blue-600 !text-white !px-3 !py-1.5 !rounded-md !text-xs hover:!bg-blue-700 disabled:!opacity-50"
                      >
                        {processingId === order?.order_unique_id
                          ? 'Accepting...'
                          : 'Accept'}
                      </button>
                    ) : (
                      <CheckCircle
                        className="!text-green-500 !inline-block !ml-2"
                        size={18}
                      />
                    )}
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

export default MyOrderPage;
