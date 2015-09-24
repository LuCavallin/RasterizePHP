var page = require('webpage').create(),
	system = require('system'),
	address, output, size;

if (system.args.length < 3 || system.args.length > 5) {
	phantom.exit(1);
} else {
	address = system.args[1];
	output = system.args[2];
	page.viewportSize = {width: 600, height: 600};
	if (system.args.length > 3 && system.args[2].substr(-4) === ".pdf") {
		size = system.args[3].split('*');
		page.paperSize = size.length === 2 ? {width: size[0], height: size[1], margin: '0px', header: {}, footer: {}}
			: {format: system.args[3], orientation: 'portrait', margin: '1cm', header: {}, footer: {}};
	} else if (system.args.length > 3 && system.args[3].substr(-2) === "px") {
		size = system.args[3].split('*');
		if (size.length === 2) {
			pageWidth = parseInt(size[0], 10);
			pageHeight = parseInt(size[1], 10);
			page.viewportSize = {width: pageWidth, height: pageHeight};
			page.clipRect = {top: 0, left: 0, width: pageWidth, height: pageHeight};
		} else {
			pageWidth = parseInt(system.args[3], 10);
			pageHeight = parseInt(pageWidth * 3 / 4, 10); // it's as good an assumption as any
			page.viewportSize = {width: pageWidth, height: pageHeight};
		}
	}
	if (system.args.length > 4) {
		page.zoomFactor = system.args[4];
	}
	page.open(address, function (status) {
		if (status !== 'success') {
			phantom.exit(1);
		} else {
			if (page.evaluate(function () {
					return typeof PhantomJSPrinting == "object";
				})) {
				paperSize = page.paperSize;
				paperSize.header.height = page.evaluate(function () {
					return PhantomJSPrinting.header.height;
				});
				paperSize.header.contents = phantom.callback(function (pageNum, numPages) {
					return page.evaluate(function (pageNum, numPages) {
						return PhantomJSPrinting.header.contents(pageNum, numPages);
					}, pageNum, numPages);
				});
				paperSize.footer.height = page.evaluate(function () {
					return PhantomJSPrinting.footer.height;
				});
				paperSize.footer.contents = phantom.callback(function (pageNum, numPages) {
					return page.evaluate(function (pageNum, numPages) {
						return PhantomJSPrinting.footer.contents(pageNum, numPages);
					}, pageNum, numPages);
				});
				page.paperSize = paperSize;
			}

			window.setTimeout(function () {
				page.render(output);
				phantom.exit();
			}, 200);
		}
	});
}
