let helpers = {};

/** App config **/
const debug = true;
helpers.role_admin = "admin";
helpers.role_manager = "manager";
helpers.role_operator = "operator";
helpers.role_stylist = "stylist";
helpers.role_customer = "customer";
helpers.roles = [
  helpers.role_admin,
  helpers.role_manager,
  helpers.role_operator,
  helpers.role_stylist,
  helpers.role_customer
];

helpers.booking_status_todo = 'todo';
helpers.booking_status_progress = 'progress';
helpers.booking_status_ended = 'ended';
helpers.booking_status_canceled = 'canceled';
helpers.booking_status_not_executed = 'not_executed';
helpers.booking_status_not_shown = 'not_shown';

// Flash messages
helpers.flash = function (flash) {
  if (flash) {
    switch (flash.type) {
      case "success":
        toastr.success(flash.message);
        break;
      case "error":
        toastr.error(flash.message);
        break;
      default:
        break;
    }
  }
};

/**
 * Global console log
 *
 * @param data
 */
helpers.lg = function (data) {
  if (debug) {
    console.log(data);
  }
};

/**
 * Price duration formatted
 *
 * @param duration
 * @returns {string}
 */
helpers.durationFormatted = function (duration) {
  switch (duration)
  {
    case '1:month': return "Ogni Mese";
    case '3:month': return "Ogni 3 Mesi";
    case '6:month': return "Ogni 6 Mesi";
    case '1:year': return "Ogni Anno";
  }
}

helpers.printServices = function (booking) {
  return booking.slots.map((slot) => {return slot.service.title}).join('; ');
}

/**
 * Capitalize first letter
 *
 * @param string
 */
helpers.capitalizeFirstLetter = function (string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
};

/**
 * Preview of n chars text
 *
 * @param text
 * @param max
 * @returns {string|*}
 */
helpers.previewText = function (text, max = 20)
{
  if (text.length <= max) return text;

  return text.slice(0, max) + '...';
}

helpers.printPrice = function (price)
{
  if ( ! isNumber(price)) return '';
  return (price % 1 !== 0) 
      ? parseFloat(price).toFixed(2).replace('.', ',')
      : parseFloat(price).toFixed(0).replace('.', ',');
}

/**
 * Datetime heplers
 */
import dayjs from "dayjs";
import "dayjs/locale/it";
import isSameOrAfter from "dayjs/plugin/isSameOrAfter";
import isSameOrBefore from "dayjs/plugin/isSameOrBefore";
import { isNumber } from "lodash";
dayjs.locale("it");
dayjs.extend(isSameOrAfter);
dayjs.extend(isSameOrBefore);
helpers.dayjs = dayjs;
helpers.datetime = {
  weekdayName: (weekdayShort) => {
    switch (weekdayShort) {
      case "sun":
        return "Domenica";
      case "mon":
        return "Lunedì";
      case "tue":
        return "Martedì";
      case "wed":
        return "Mercoledì";
      case "thu":
        return "Giovedì";
      case "fri":
        return "Venerdì";
      case "sat":
        return "Sabato";
    }
  },
  weekdayIndex: (weekdayShort) => {
    switch (weekdayShort) {
      case "sun":
        return 7;
      case "mon":
        return 1;
      case "tue":
        return 2;
      case "wed":
        return 3;
      case "thu":
        return 4;
      case "fri":
        return 5;
      case "sat":
        return 6;
    }
  },
};

export default helpers;
