import {_TableItem} from "../interfaces/inter-sp";

export default class TableHelper {

  public static addColumn(table: _TableItem, index: number = null): _TableItem {
    const noRowsYet = table.length < 1;
    console.log('Add column',{index});
    if (null === index) {
      if (noRowsYet) {
        table.push(['']);
      } else {
        table.forEach((row, i) => {
          row.push('');
        });
      }
    } else if (0 === index) {
      table.forEach((row, i) => {
        row.unshift('');
      });
      console.log('column added');
    } else {
      table.forEach((row, i) => {
        const newRow = [
          ...row.slice(0, index),
          '',
          ...row.slice(index),
        ];
        table[i]     = newRow;
        console.log('col >', {newRow, index, table})
      });
    }
    console.log({table});
    return table;
  }

  public static addRow(table: _TableItem, index: number = null): _TableItem {
    console.log('addRow', index);
    const noRowsYet = table.length < 1;
    if (null === index) {
      if(noRowsYet){
        table.push(['']);
      }else{
        const columnCount = table[0].length;
        const row         = [];
        for (let a = 0; a < columnCount; a++) {
          row.push('');
        }
        table.push(row);
      }
    } else if (0 === index) {
      const columnCount = table[0].length;
      const row         = [];
      for (let a = 0; a < columnCount; a++) {
        row.unshift('');
      }
      table.unshift(row);
      console.log('row added');
    } else if (null !== index) {
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

  public static deleteRow(table: _TableItem, index: number): _TableItem {
    console.log('delete row', {index});
    table.splice(index, 1);
    return table;
  }

  public static deleteColumns(table: _TableItem, index: number): _TableItem {
    console.log('delete columns', {index});
    table.forEach((row, i) => {
      row.splice(index, 1);
    });
    return table;
  }

}