  // Fetch data from the stats endpoint
  fetch('/stats')
  .then(response => response.json())
  .then(data => {
      // Populate the data into the respective elements
      document.getElementById('todayCollection').textContent = `₹${data.todayCollection}`;
      document.getElementById('totalInvoiceToday').textContent = data.totalInvoiceToday;
      document.getElementById('totalCompleteOrder').textContent = data.totalCompleteOrderToday;
      document.getElementById('totalRejectOrder').textContent = data.totalRejectOrderToday;

      document.getElementById('weeklyCollection').textContent = `₹${data.weeklyCollection}`;
      document.getElementById('weeklyInvoice').textContent = data.totalInvoiceWeekly;
      document.getElementById('weeklyComplete').textContent = data.totalCompleteOrderWeekly;
      document.getElementById('weeklyReject').textContent = data.totalRejectOrderWeekly;

      document.getElementById('monthlyCollection').textContent = `₹${data.monthlyCollection}`;
      document.getElementById('monthlyInvoice').textContent = data.totalInvoiceMonthly;
      document.getElementById('monthlyComplete').textContent = data.totalCompleteOrderMonthly;
      document.getElementById('monthlyReject').textContent = data.totalRejectOrderMonthly;
  })
  .catch(error => {
      console.error('Error fetching stats:', error);
  });
