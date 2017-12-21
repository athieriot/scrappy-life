package models

import reactivemongo.bson.{BSONDocument, BSONValue}

case class Criteria(field: String, matcher: String, value: Option[BSONValue])

object Criteria {
  def toQuery(criterion: List[Criteria]): BSONDocument = {
    val query = criterion.flatMap { c =>
      c.value match {
        case None => None
        case Some(_) => c.matcher match {
          case "$text" => Some(BSONDocument("$text" -> BSONDocument("$search" -> c.value)))
          case _ => Some(BSONDocument(c.field -> BSONDocument(c.matcher -> c.value)))
        }
      }
    }

    if (query.isEmpty) BSONDocument() else BSONDocument("$and" -> query)
  }
}
