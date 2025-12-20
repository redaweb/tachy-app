console.log('Test Puppeteer...');
console.log('Node version:', process.version);

const puppeteer = require('puppeteer');

(async () => {
  try {
    console.log('Launching browser...');
    const browser = await puppeteer.launch({
      headless: 'new',
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    console.log('Browser launched successfully!');
    
    const page = await browser.newPage();
    await page.setContent('<h1>Test Page</h1><p>Hello World</p>');
    
    const pdf = await page.pdf({ format: 'A4' });
    console.log('PDF generated, size:', pdf.length, 'bytes');
    
    await browser.close();
    console.log('Test completed successfully!');
    
  } catch (error) {
    console.error('Error:', error.message);
    console.error('Stack:', error.stack);
    process.exit(1);
  }
})();