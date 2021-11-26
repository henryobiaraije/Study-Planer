import {_TableItem} from "../interfaces/inter-sp";

export default class TableHelper {

  public static addColumn(table: _TableItem, index: number): _TableItem {
    const noRowsYet = table.length < 1;
    if (0 === index) {
      if (noRowsYet) {
        table.push(['']);
      } else {
        table.forEach((row, i) => {
          row.unshift('');
        });
        console.log('column added');
      }
    } else {
      table.forEach((row, i) => {
        const newRow = [
          ...row.slice(0, i),
          '',
          ...row.slice(i),
        ];
        table[i]     = newRow;
        console.log('col >', {newRow, index, table})
      });

    }
    console.log({table});
    return table;
  }

  public static addRow(table: _TableItem, index: number): _TableItem {
    const noRowsYet = table.length < 1;
    if (0 === index) {
      if (noRowsYet) {
        table.push(['']);
      } else {
        const columnCount = table[0].length;
        const row         = [];
        for (let a = 0; a < columnCount; a++) {
          row.push('');
        }
        table.push(row);
        console.log('row added');
      }
    } else {
      const columnCount = table[0].length;
      const newRow      = [];
      for (let a = 0; a < columnCount; a++) {
        newRow.push('');
      }
      table = [
        ...table.slice(0, index),
        newRow,
        ...table.slice(index),
      ]
      console.log('adding > 0', {table, index});
    }
    console.log({table, index});
    return table;
  }

}