import dayjs from "dayjs";

export function useTableUtils() {
  function getCellAlignment(alignment) {
    return {
      left: alignment != "right" && alignment != "center",
      right: alignment == "right",
      center: alignment == "center",
    };
  }

  function getClasses(classes, item) {
    if (typeof classes == "string") return { [classes]: true };
    if (typeof classes == "function") return classes(item);
    return {};
  }

  function getData(item, key) {
    if (!key.includes(".")) return item[key];
    return findData(item, key.split("."));
  }

  function findData(item, keys) {
    try {
      const currentKey = keys[0];
      if (keys.length == 1) return item[currentKey];
      if (currentKey == "*" && Array.isArray(item)) {
        const results = [];
        for (let i = 0; i < item.length; i++) {
          results.push(findData(item[i], keys.slice(1)));
        }
        return results.join(", ");
      }
      return findData(item[currentKey], keys.slice(1));
    } catch (error) {
      return null;
    }
  }

  function getFormattedData(value, formatType) {
    if (value === null) return "-";
    switch (formatType) {
      case "currency":
        return `${new Intl.NumberFormat("it-IT").format(value)} €`;
      case "date":
        return dayjs(value).format("DD/MM/YYYY");
      case "datetime":
        return dayjs(value).format("DD/MM/YYYY HH:mm");
      case "active":
        return (value)
            ? '<span class="block h-4 w-4 rounded-full bg-bb-green-300 ring-0 ring-white"></span>'
            : '<span class="block h-4 w-4 rounded-full bg-bb-red-400 ring-0 ring-white"></span>'
      default:
        return value;
    }
  }

  return {
    getCellAlignment,
    getClasses,
    getData,
    getFormattedData,
  };
}
