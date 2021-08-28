const fs = require("fs");
const puppeteer = require("puppeteer");
const pages = require("./pages.json");

const width = 1400;
const height = 768;

const folder = "public/static/help";

async function captureScreenshots() {

    let browser = null;

    try {

        browser = await puppeteer.launch({
            headless: true,
        });

        const page = await browser.newPage();

        await page.setViewport({
            width: width,
            height: height,
        });

        // Open Start Page
        await page.goto("http://tracking.localhost");
        await page.screenshot({path: `${folder}/1_login.jpg`});

        // Login
        await page.type('#inputUsername', "admin");
        await page.type('#inputPassword', "admin");
        page.click('#submit');
        await page.waitForNavigation();

        for (page_data of pages) {
            await makeScreenshot(page, page_data);
        }
        
        await page.goto("http://tracking.localhost/finances/edit/");
        await page.type("#inputDescription", "Test Entry")
        await page.type("#inputValue", "4")
        await page.keyboard.press('Enter');
        await page.waitForNavigation();
        await page.screenshot({path: `${folder}/4_finances-13.jpg`});
        
        await page.goto("http://tracking.localhost/finances/budgets/");
        await page.screenshot({path: `${folder}/4_finances-14.jpg`});

        await page.goto("http://tracking.localhost/crawlers/ABCabc123/view/");
        await page.click('#crawler_links > li:first-child');
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/7_crawlers-5b.jpg`});
        
        await page.setViewport({
            width: width,
            height: 1200
        });
        await page.goto("http://tracking.localhost/trips/ABCabc123/view/");
        await page.click('.leaflet-routing-collapse-btn');
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/9_trips-5.jpg`});
        await page.setViewport({
            width: width,
            height: height
        });
        await page.waitForTimeout(1000);

        await page.goto("http://tracking.localhost/location/?from=2020-01-01&to=2021-12-31");
        await page.waitForTimeout(1000);
        await page.click('#layerDirections');
        await page.click('#show-filter');
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/3_location_history-3.jpg`});

        await page.goto("http://tracking.localhost/profile/favorites/");
        await page.setViewport({
            width: 500,
            height: 500
        });
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/2_mobilefavorites-3.jpg`});
        await page.click('#menu-toggle');
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/2_mobilefavorites-4.jpg`});
        await page.setViewport({
            width: width,
            height: height
        });
        await page.waitForTimeout(1000);

        await page.goto("http://tracking.localhost/profile/frontpage/");
        await page.select("select", "last_refuel")
        await page.click('#add-widget');
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/2_frontpage-2.jpg`});

        // Login as user
        await page.goto("http://tracking.localhost/logout");
        await page.type('#inputUsername', "user");
        await page.type('#inputPassword', "user");
        page.click('#submit');
        await page.waitForNavigation();

        await page.goto("http://tracking.localhost/profile/frontpage/");
        await page.screenshot({path: `${folder}/2_frontpage-3.jpg`});
        
        await page.goto("http://tracking.localhost/notifications/manage/");
        await page.setViewport({
            width: width,
            height: 1000
        });
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/2_notifications-4.jpg`});
        await page.setViewport({
            width: width,
            height: height
        });
        await page.waitForTimeout(1000);
        
        await page.goto("http://tracking.localhost/recipes/");
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/12_recipes-1.jpg`});
        
        await page.goto("http://tracking.localhost/recipes/cookbooks/ABCabc123/view/");
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/12_recipes-7.jpg`});
        
        await page.goto("http://tracking.localhost/recipes/mealplans/ABCabc123/view/?from=2021-08-23&to=2021-08-29");
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/12_recipes-13.jpg`});
        
        await page.click('.create-notice');
        await page.waitForTimeout(1000);
        await page.screenshot({path: `${folder}/12_recipes-14.jpg`});

    } catch (err) {
        console.log(`❌ Error: ${err.message}`);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

async function makeScreenshot(page, page_data){    

    await page.setViewport({
        width: width,
        height: height
    });

    await page.goto(page_data["url"]);

    let page_height = height;
    if("height" in page_data){
        page_height = page_data["height"];
    }
    let page_width = width;
    if("width" in page_data){
        page_width = page_data["width"];
    }
    await page.setViewport({
        width: page_width,
        height: page_height
    });

    await page.waitForTimeout(1000);
    await page.screenshot({path: `${folder}/${page_data["name"]}.jpg`});

    console.log(`✅ ${page_data["name"]} - (${page_data["url"]})`);
}

async function captureScreenshotsNotHeadless() {

    let browser = null;

    try {

        browser = await puppeteer.launch({
            headless: false,
        });

        const page = await browser.newPage();

        const context = browser.defaultBrowserContext();
        await context.overridePermissions("http://tracking.localhost", ['notifications']);

        await page.setViewport({
            width: width,
            height: height,
        });

        // Open Start Page
        await page.goto("http://tracking.localhost");
        await page.screenshot({path: `${folder}/1_login.jpg`});

        // Login
        await page.type('#inputUsername', "admin");
        await page.type('#inputPassword', "admin");
        page.click('#submit');
        await page.waitForNavigation();

        // Push Notifications
        await page.goto("http://tracking.localhost/notifications/manage/");
        await page.waitForTimeout(5000);
        await page.screenshot({path: `${folder}/2_notifications-2.jpg`});
        
        await page.setViewport({
            width: width,
            height: 1000
        });
        
        await page.click('#enable_notifications');
        await page.waitForTimeout(5000);

        await page.screenshot({path: `${folder}/2_notifications-1.jpg`});
        await page.setViewport({
            width: width,
            height: height
        });
        await page.waitForTimeout(1000);

    } catch (err) {
        console.log(`❌ Error: ${err.message}`);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

async function start(){
    await captureScreenshots();
    await captureScreenshotsNotHeadless();
}

start();


