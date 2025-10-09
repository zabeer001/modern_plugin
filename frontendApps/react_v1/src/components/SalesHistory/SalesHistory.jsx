import React from 'react';


const orders = [
  { customer: '1140', product: 'T Shirt', image: 'https://via.placeholder.com/40', orderId: '275936', totalPrice: '$250', date: '05/31/2025 03:18pm', status: 'completed' },
  { customer: '1140', product: 'T Shirt', image: 'https://via.placeholder.com/40', orderId: '275936', totalPrice: '$250', date: '05/31/2025 03:18pm', status: 'processing' },
  { customer: '1140', product: 'T Shirt', image: 'https://via.placeholder.com/40', orderId: '275936', totalPrice: '$250', date: '05/31/2025 03:18pm', status: 'completed' },
  { customer: '1140', product: 'T Shirt', image: 'https://via.placeholder.com/40', orderId: '275936', totalPrice: '$250', date: '05/31/2025 03:18pm', status: 'shipping' },
  { customer: '1140', product: 'T Shirt', image: 'https://via.placeholder.com/40', orderId: '275936', totalPrice: '$250', date: '05/31/2025 03:18pm', status: 'completed' },
];

const SalesHistory = () => {
  return (
    <section className="sales-history">
      <h2>Sales History</h2>
      <table className="sales-table">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Product</th>
            <th>Order ID</th>
            <th>Total Price</th>
            <th>Date</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {orders.map((order, index) => (
            <tr key={index}>
              <td>{order.customer}</td>
              <td>
                <div className="product-info">
                  <img src={order.image} alt={order.product} />
                  {order.product}
                </div>
              </td>
              <td>{order.orderId}</td>
              <td>{order.totalPrice}</td>
              <td>{order.date}</td>
              <td>
                <select className={`status-dropdown ${order.status}`}>
                  <option value="completed" selected={order.status === 'completed'}>Completed</option>
                  <option value="processing" selected={order.status === 'processing'}>Processing</option>
                  <option value="shipping" selected={order.status === 'shipping'}>Shipping</option>
                  <option value="cancelled" selected={order.status === 'cancelled'}>Cancelled</option>
                </select>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <div className="pagination">
        <span>Showing 1 to 5 of 12 results</span>
        <ul>
          <li className="disabled"><a href="#prev"><i className="fas fa-chevron-left"></i></a></li>
          <li className="active"><a href="#page1">1</a></li>
          <li><a href="#page2">2</a></li>
          <li><a href="#page3">3</a></li>
          <li><a href="#dots">...</a></li>
          <li><a href="#page8">8</a></li>
          <li><a href="#next"><i className="fas fa-chevron-right"></i></a></li>
        </ul>
      </div>
    </section>
  );
};

export default SalesHistory;
