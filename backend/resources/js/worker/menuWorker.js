self.onmessage = function(e) {
    console.log('Worker received data:', e.data);
    const { menuItems, topItems } = e.data;
    
    const processedMenuItems = menuItems.map(item => ({
      ...item,
      Price: item.Price * 1.1
    }));
    
    const processedTopItems = topItems.map(item => ({
      ...item,
      Price: item.Price * 1.1
    }));
    
    self.postMessage({ processedMenuItems, processedTopItems });
    console.log('Worker processed data and sent back:', { processedMenuItems, processedTopItems });
  };