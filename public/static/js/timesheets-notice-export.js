"use strict";const wrapper=document.querySelector("#timesheetNoticeWrapper");document.querySelector("#wordExport").addEventListener("click",(function(e){e.preventDefault();let t=wrapper.dataset.sheetname?wrapper.dataset.sheetname:wrapper.dataset.projectname;const r=[];let n=Array.from(wrapper.querySelectorAll(".timesheet-notice-wrapper:not(.hidden)"));for(const e of n){let t=e.querySelector(".sheet_customer"),a=t?t.innerHTML.replace(/&nbsp;/g," "):"",c=e.querySelector(".sheet_categories"),o=c?c.innerHTML.replace(/&nbsp;/g," "):"",l=e.querySelector(".sheet_title").innerHTML;const i=new docx.Paragraph({heading:docx.HeadingLevel.HEADING_1,children:[new docx.TextRun({text:l})]}),p=new docx.Paragraph({children:[new docx.TextRun({text:a,italics:!0,size:24})],spacing:{after:200}}),s=new docx.Paragraph({children:[new docx.TextRun({text:o,italics:!0,size:24})],spacing:{after:200}});r.push(i),r.push(p),r.push(s),e.querySelectorAll(".timesheet-notice-field:not(.hidden)").forEach((function(e){e.querySelectorAll('input[type="text"], textarea, select, p.notice-field').forEach((function(e){let t=[new docx.TextRun({text:e.previousElementSibling.innerHTML,underline:{}})];const n=("p"===e.tagName.toLowerCase()?e.innerHTML.replace(/<br ?\/?>/g,"\n").replaceAll("&amp;","&").replaceAll("&gt;",">").replaceAll("&lt;","<"):e.value).split("\n").map((e=>new docx.TextRun({text:e,break:1})));t.push(...n);const a=new docx.Paragraph({children:t,spacing:{after:400}});r.push(a)}))})),n.length-1!==n.indexOf(e)&&r.push(new docx.Paragraph({children:[new docx.PageBreak]}))}const a=new docx.Document({styles:{default:{heading1:{run:{size:48,color:"000000",font:"Calibri"},paragraph:{spacing:{after:120}}}},paragraphStyles:[{name:"Normal",run:{size:24,font:"Calibri"}}]},sections:[{properties:{},children:r}]});docx.Packer.toBlob(a).then((e=>{saveAs(e,t+"_Export.docx")}))}));