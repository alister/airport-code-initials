
var knownAirportCodes = require('../assets/airportCodesLookup');

module.exports = function(airportCode) {
  /*if (airportCode === 'ace') {
    return 'A.C.E.!';
  }*/
  const codeUpper = airportCode.toUpperCase();
  const name = knownAirportCodes[codeUpper];
  if (name === undefined) {
    return `Sorry, '${codeUpper}' is not known as an airport code`;
  }

  return `'${codeUpper}' is the IATA airport code for
  '${name}'`;
};
