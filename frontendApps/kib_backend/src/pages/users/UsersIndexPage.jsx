import React, { useEffect, useState } from 'react';

function OrderIndexPage() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [categoryFilter, setCategoryFilter] = useState('all');

  useEffect(() => {
    const fetchOrders = async () => {
      try {
        const token = localStorage.getItem('jwt_token');
        console.log(token);

        const response = await fetch('https://pickmybeauty.de/wp-json/kibsterlp-admin/v1/orders', {
          headers: {
            'Authorization': `Bearer ${token}`,
          },
        });

        if (!response.ok) throw new Error(`HTTP error! ${response.status}`);
        const data = await response.json();
        setOrders(data.orders || []);
      } catch (error) {
        console.error('Error fetching orders:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, []);

  // Filter orders
  const filteredOrders = orders.filter((order) => {
    const matchesSearch =
      order.order_title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      order.id?.toString().includes(searchTerm) ||
      order.zip_code?.includes(searchTerm);
    const matchesStatus = statusFilter === 'all' || order.payment_status === statusFilter;
    const matchesCategory = categoryFilter === 'all' || order.category_id === categoryFilter;
    return matchesSearch && matchesStatus && matchesCategory;
  });

  // Format currency
  const formatCurrency = (amount) =>
    new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount || 0);

  // Format date
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

  if (loading)
    return (
      <div className="!flex !justify-center !items-center !min-h-screen !bg-gradient-to-br !from-blue-50 !to-indigo-50">
        <div className="!text-center">
          <div className="!animate-spin !rounded-full !h-12 !w-12 !border-b-2 !border-blue-600 !mx-auto !mb-4"></div>
          <p className="!text-gray-600 !text-lg !font-medium">Loading orders...</p>
        </div>
      </div>
    );

  return (
    <div className="!max-w-7xl !mx-auto !mt-4 !p-4">
      {/* Toolbar */}
      <div className="!bg-white !rounded-xl !shadow-sm !border !border-gray-200 !p-4 !mb-6 !flex !flex-wrap !items-center !justify-between">
        {/* Left Controls */}
        <div className="!flex !items-center !space-x-3">
          {/* Search */}
          <div className="!relative !w-52">
            <input
              type="text"
              placeholder="Search..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
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
            value={categoryFilter}
            onChange={(e) => setCategoryFilter(e.target.value)}
            className="!border !border-gray-300 !rounded-lg !px-[25px] !py-1.5 !text-sm focus:!ring-2 focus:!ring-blue-500 focus:!border-blue-500"
          >
            <option value="all">All Categories</option>
            <option value="1">Keyboard</option>
            <option value="2">Makeup</option>
            <option value="3">Hair</option>
          </select>
        </div>

        {/* Right Filter */}
        <div className="!flex !items-center !space-x-3">
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="!border !border-gray-300 !rounded-lg !px-[25px] !py-1.5 !text-sm focus:!ring-2 focus:!ring-blue-500 focus:!border-blue-500"
          >
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
          </select>

          <div className="!text-sm !text-gray-600 !bg-gray-50 !border !border-gray-200 !rounded-lg !px-3 !py-1">
            {filteredOrders.length} orders
          </div>
        </div>
      </div>

      {/* Orders Table */}
      <div className="!bg-white !rounded-xl !shadow-sm !border !border-gray-200 !overflow-hidden">
        <table className="!min-w-full !divide-y !divide-gray-200">
          <thead className="!bg-gray-50">
            <tr>
              {['Order', 'Vendor', 'Status', 'Budget & Price', 'Location', 'Created'].map((h) => (
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
            {filteredOrders.map((order, index) => (
              <tr
                key={order.id || index}
                className="!hover:bg-gray-50 !transition-colors !duration-100 !cursor-pointer"
              >
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
                <td className="!px-6 !py-3 !text-sm !text-gray-700">Vendor {order.vendor_id}</td>
                <td className="!px-6 !py-3">
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
                <td className="!px-6 !py-3 !text-sm !text-gray-500">{formatDate(order.created_at)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default OrderIndexPage;
