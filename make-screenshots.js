const fs = require("fs");
const puppeteer = require("puppeteer");
const pages = require("./pages.json");

const width = 1400;
const height = 768;

const lang = "de";
const folder = `public/static/help/${lang}`;

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
        await page.screenshot({ path: `${folder}/1_login.jpg` });

        // Login
        await page.type('#inputUsername', "admin");
        await page.type('#inputPassword', "admin");
        page.click('#submit');
        await page.waitForNavigation();
        console.log(`✅ Login`);

        for (page_data of pages) {
            await makeScreenshot(page, page_data);
        }
        let file = ``

        file = `4_finances-13.jpg`
        await page.goto("http://tracking.localhost/finances/edit/");
        await page.type("#inputDescription", "Test Entry")
        await page.type("#inputValue", "4")
        await page.keyboard.press('Enter');
        await page.waitForNavigation();
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `4_finances-14.jpg`
        await page.goto("http://tracking.localhost/finances/budgets/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        await page.goto("http://tracking.localhost/finances/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.click('.btn-delete');
        await page.keyboard.press('Enter');
        await page.waitForNavigation();
        console.log(`✅ Delete created finance entry`);

        file = `7_crawlers-5b.jpg`
        await page.goto("http://tracking.localhost/crawlers/ABCabc123/view/");
        await page.click('#crawler_links > li:first-child');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `6_boards-2.jpg`
        await page.goto("http://tracking.localhost/boards/view/ABCabc123");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.click('.board-card[data-card="1"]');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);
        await page.click('#card-close-btn');

        file = `9_trips-5.jpg`
        await page.setViewport({
            width: width,
            height: 1200
        });
        await page.goto("http://tracking.localhost/trips/ABCabc123/view/");
        await page.click('.leaflet-routing-collapse-btn');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        await page.setViewport({
            width: width,
            height: height
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));

        file = `3_location_history-3.jpg`
        await page.goto("http://tracking.localhost/location/?from=2020-01-01&to=2021-12-31");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.click('#layerDirections');
        await page.click('#show-filter');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `2_mobilefavorites-3.jpg`
        await page.goto("http://tracking.localhost/profile/favorites/");
        await page.setViewport({
            width: 400,
            height: 600
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        await page.setViewport({
            width: width,
            height: height
        });

        file = `2_frontpage-2.jpg`
        await page.goto("http://tracking.localhost/profile/frontpage/");
        await page.select("select", "last_refuel")
        await page.click('#add-widget');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `12_recipes-1.jpg`
        await page.goto("http://tracking.localhost/recipes/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `12_recipes-7.jpg`
        await page.goto("http://tracking.localhost/recipes/cookbooks/ABCabc123/view/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `12_recipes-13.jpg`
        await page.goto("http://tracking.localhost/recipes/mealplans/ABCabc123/view/?from=2021-08-23&to=2021-08-29");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `12_recipes-14.jpg`
        await page.click('.create-notice');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `12_recipes-16.jpg`
        await page.goto("http://tracking.localhost/recipes/shoppinglists/ABCabc123/view/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `12_recipes-17.jpg`
        await page.type('#addGroceryToList_name', 'Test');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `12_recipes-18.jpg`
        await page.reload();
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.click('.shopping-list-entry input[data-id="1"]');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        await page.click('.shopping-list-entry input[data-id="1"]');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        console.log(`✅ ${file}`);

        file = `10_timesheets-20.jpg`
        await page.goto("http://tracking.localhost/timesheets/GHIghi789/sheets/notice/6/edit/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-21.jpg`
        await page.type('#input-modal input[type="password"]', '123');
        await page.keyboard.press('Enter');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-22.jpg`
        await page.setViewport({
            width: width,
            height: 1000
        });
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/sheets/notice/1/edit/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.type('#input-modal input[type="password"]', '123');
        await page.keyboard.press('Enter');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-23.jpg`
        await page.setViewport({
            width: width,
            height: height
        });
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/notice/edit/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-24.jpg`
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/customers/notice/1/edit/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-27.jpg`
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/sheets/notice/1/view/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-28.jpg`
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/customers/notice/1/view/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-25.jpg`
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/noticepassword/recovery");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-32.jpg`
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/noticepassword/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.click('#forgotPassword');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-26.jpg`
        await page.setViewport({
            width: width,
            height: 1000
        });
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/calendar/?from=2020-01-01");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-29.jpg`
        await page.click('.fc-event-main');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });

        file = `10_timesheets-31.jpg`
        await page.setViewport({
            width: width,
            height: 1300
        });
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/sheets/edit/5");
        await page.click('#checkboxHappened');
        await page.click('#checkBoxRepeat');
        await page.focus('#inputCount');
        await page.keyboard.down('Control');
        await page.keyboard.press('A');
        await page.keyboard.up('Control');
        await page.keyboard.press('Backspace');
        await page.type("#inputCount", "3");
        await page.type("#inputMultiplier", "1");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-33.jpg`
        await page.setViewport({
            width: width,
            height: height
        });
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/export/?from=2020-01-01&to=2025-01-01");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-34.jpg`
        await page.click('#radioHTML');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-35.jpg`
        await page.setViewport({
            width: width,
            height: 1500
        });
        await page.click('input[type="submit"]');
        await page.waitForNavigation();
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-36.jpg`
        await page.setViewport({
            width: width,
            height: height
        });
        await page.goto("http://tracking.localhost/timesheets/ABCabc123/export/?from=2020-01-01&to=2025-01-01");
        await page.click('#radioHTMLOverview');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-37.jpg`
        await page.click('#noticefieldsfilter div.selectr-container');
        await page.click('#noticefieldsfilter li.selectr-option');
        await page.keyboard.press('Escape');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `10_timesheets-38.jpg`
        await page.click('input[type="submit"]');
        await page.waitForNavigation();
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        // Login as user
        await page.goto("http://tracking.localhost/logout");
        await page.type('#inputUsername', "user");
        await page.type('#inputPassword', "user");
        page.click('#submit');
        await page.waitForNavigation();

        file = `2_frontpage-3.jpg`
        await page.goto("http://tracking.localhost/profile/frontpage/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        await page.setViewport({
            width: width,
            height: height
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));

        await page.goto("http://tracking.localhost/logout");

    } catch (err) {
        console.log(`❌ Error: ${err.message}`);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

async function makeScreenshot(page, page_data) {

    await page.setViewport({
        width: width,
        height: height
    });

    await page.goto(page_data["url"]);

    let page_height = height;
    if ("height" in page_data) {
        page_height = page_data["height"];
    }
    let page_width = width;
    if ("width" in page_data) {
        page_width = page_data["width"];
    }
    await page.setViewport({
        width: page_width,
        height: page_height
    });

    await new Promise((resolve) => setTimeout(resolve, 1000));
    await page.screenshot({ path: `${folder}/${page_data["name"]}.jpg` });

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

        await page.setViewport({
            width: width,
            height: height,
        });

        // Open Start Page
        await page.goto("http://tracking.localhost");
        await new Promise((resolve) => setTimeout(resolve, 1000));        
        // Login
        await page.type('#inputUsername', "admin");
        await page.type('#inputPassword', "admin");
        page.click('#submit');
        await page.waitForNavigation();

        // Push Notifications

        await context.overridePermissions("http://tracking.localhost", []);

        file = `2_notifications-3.jpg`;
        await page.setViewport({
            width: width,
            height: 1400
        });
        await page.goto("http://tracking.localhost/notifications/manage/");
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `2_notifications-4.jpg`;
        await page.setViewport({
            width: width,
            height: 1800
        });
        await page.goto("http://tracking.localhost/notifications/manage/");
        await page.type('#ifttt_url', 'test');
        await page.click('#ifttt_url_save');
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        await page.click('#ifttt_url_remove');
        await new Promise((resolve) => setTimeout(resolve, 1000));

        file = `2_notifications-1.jpg`;
        await context.overridePermissions("http://tracking.localhost", ['notifications']);
        await page.setViewport({
            width: width,
            height: 1500
        });

        await page.goto("http://tracking.localhost/notifications/manage/");
        await new Promise((resolve) => setTimeout(resolve, 5 * 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

        file = `2_notifications-2.jpg`;
        await page.click('#enable_notifications');
        await new Promise((resolve) => setTimeout(resolve, 60 * 1000));
        await page.screenshot({ path: `${folder}/${file}` });
        console.log(`✅ ${file}`);

    } catch (err) {
        console.log(`❌ Error: ${err.message}`);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

async function start() {
    await captureScreenshots();
    await captureScreenshotsNotHeadless();
}

start();