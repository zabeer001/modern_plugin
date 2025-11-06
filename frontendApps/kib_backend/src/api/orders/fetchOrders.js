import { backendUrl } from "../../../env";



export async function fetchOrders({ page = 1, search = '', token, category_id, sharing_status , payment_status}) {
  try {
    const params = new URLSearchParams();
    params.set('page', String(page));
    if (search) params.set('search', search);



    // ✅ only include if provided and not "all"
    if (category_id && category_id !== 'all') {
      params.set('category_id', String(category_id));
    }

     if (sharing_status && sharing_status !== 'all') {
      // console.log(sharing_status);
      
      params.set('sharing_status', String(sharing_status));
    }

     if (payment_status && payment_status !== 'all') {
      // console.log(sharing_status);
      
      params.set('payment_status', String(payment_status));
    }

    const url = `${backendUrl}/wp-json/kibsterlp-admin/v1/orders?${params.toString()}`;

    const response = await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error! ${response.status}`);
    }

    const data = await response.json();

    return {
      orders: data.orders || [],
      totalPages: data.total_pages || 1,
    };
  } catch (error) {
    console.error('Error fetching orders:', error);
    throw error;
  }
}